<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApprovePermissionRequest;
use App\Http\Requests\RejectPermissionRequest;
use App\Http\Requests\BulkApprovalRequest;
use App\Models\PermissionRequest;
use App\Models\Approval;
use App\Services\PermissionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class ApprovalController extends Controller
{
    public function __construct(
        private PermissionService $permissionService
    ) {}

    /**
     * Display pending approvals for the current user
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Filtros
        $status = $request->get('status', 'pending');
        $department = $request->get('department');
        $permissionType = $request->get('permission_type');
        
        $pendingApprovals = $this->getPendingApprovals($user, $status, $department, $permissionType);
        
        // Historial de aprobaciones del usuario
        $approvalHistory = Approval::with(['permissionRequest.user', 'permissionRequest.permissionType'])
            ->where('approver_id', $user->id)
            ->whereIn('status', [Approval::STATUS_APPROVED, Approval::STATUS_REJECTED])
            ->latest('approved_at')
            ->paginate(10, ['*'], 'history_page');

        // Estadísticas del dashboard
        $stats = $this->getApprovalStats($user);

        // Datos para filtros
        $departments = $this->getDepartmentsForFilter($user);
        $permissionTypes = \App\Models\PermissionType::active()->get(['id', 'name']);

        return view('approvals.index', compact(
            'pendingApprovals', 
            'approvalHistory', 
            'stats', 
            'departments', 
            'permissionTypes',
            'status',
            'department',
            'permissionType'
        ));
    }

    /**
     * Show approval form
     */
    public function show(PermissionRequest $permission)
    {
        $user = Auth::user();
        
        // Verificar que el usuario puede aprobar esta solicitud
        if (!$user->canApprove($permission)) {
            abort(403, 'No tiene permisos para aprobar esta solicitud.');
        }

        // Verificar que la solicitud está en el estado correcto
        $expectedStatus = $this->getExpectedStatusForApprover($user, $permission);
        if ($permission->status !== $expectedStatus) {
            return redirect()->route('approvals.index')
                ->with('warning', 'Esta solicitud ya no está pendiente de su aprobación.');
        }

        $permission->load([
            'user.department', 
            'user.immediateSupervisor',
            'permissionType', 
            'documents', 
            'approvals.approver'
        ]);

        // Obtener historial de permisos del solicitante (últimos 6 meses)
        $userPermissionHistory = $permission->user->permissionRequests()
            ->with('permissionType')
            ->where('id', '!=', $permission->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->limit(10)
            ->get();

        // Estadísticas del solicitante
        $userStats = $this->permissionService->getUserPermissionStats(
            $permission->user,
            now()->year,
            now()->month
        );

        return view('approvals.show', compact('permission', 'userPermissionHistory', 'userStats'));
    }

    /**
     * Process approval
     */
    public function approve(ApprovePermissionRequest $request, PermissionRequest $permission)
    {
        $user = Auth::user();

        \Log::info('Approval attempt', [
            'user_id' => $user->id,
            'permission_id' => $permission->id,
            'permission_status' => $permission->status,
            'permission_user_id' => $permission->user_id,
            'user_immediate_supervisor_id' => $permission->user->immediate_supervisor_id ?? null,
        ]);

        // Verificar estado actual
        $expectedStatus = $this->getExpectedStatusForApprover($user, $permission);

        \Log::info('Expected status check', [
            'expected' => $expectedStatus,
            'actual' => $permission->status,
            'match' => $permission->status === $expectedStatus,
        ]);

        if ($permission->status !== $expectedStatus) {
            \Log::warning('Status mismatch in approval', [
                'expected' => $expectedStatus,
                'actual' => $permission->status,
            ]);
            return redirect()->route('approvals.index')
                ->with('error', 'Esta solicitud ya no está pendiente de su aprobación.');
        }

        try {
            $result = $this->permissionService->approve(
                $permission,
                $user,
                $request->validated()['comments'] ?? null
            );

            \Log::info('Approval result', ['result' => $result]);

            if ($result) {
                $message = $permission->status === PermissionRequest::STATUS_APPROVED
                    ? 'Solicitud aprobada exitosamente.'
                    : 'Solicitud aprobada y enviada a RRHH para aprobación final.';

                return redirect()->route('approvals.index')
                    ->with('success', $message);
            }

            \Log::error('Approval returned false');
            return back()->with('error', 'Error al aprobar la solicitud. Por favor, revise los logs.');

        } catch (\Exception $e) {
            \Log::error('Exception in approval', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'Error al procesar la aprobación: ' . $e->getMessage());
        }
    }

    /**
     * Process rejection
     */
    public function reject(RejectPermissionRequest $request, PermissionRequest $permission)
    {
        $user = Auth::user();
        
        // Verificar estado actual
        $expectedStatus = $this->getExpectedStatusForApprover($user, $permission);
        if ($permission->status !== $expectedStatus) {
            return redirect()->route('approvals.index')
                ->with('error', 'Esta solicitud ya no está pendiente de su aprobación.');
        }

        try {
            $result = $this->permissionService->reject(
                $permission,
                $user,
                $request->validated()['comments']
            );

            if ($result) {
                return redirect()->route('approvals.index')
                    ->with('success', 'Solicitud rechazada exitosamente.');
            }
            
            return back()->with('error', 'Error al rechazar la solicitud.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al procesar el rechazo: ' . $e->getMessage());
        }
    }

    /**
     * Process bulk approvals/rejections
     */
    public function bulkAction(BulkApprovalRequest $request)
    {
        $user = Auth::user();
        $validated = $request->validated();
        
        $permissionIds = $validated['permission_ids'];
        $action = $validated['action'];
        $comments = $validated['bulk_comments'] ?? null;

        $processedCount = 0;
        $errors = [];

        DB::beginTransaction();
        
        try {
            foreach ($permissionIds as $permissionId) {
                $permission = PermissionRequest::find($permissionId);
                
                if (!$permission || !$user->canApprove($permission)) {
                    $errors[] = "No puede procesar la solicitud #{$permissionId}";
                    continue;
                }

                $expectedStatus = $this->getExpectedStatusForApprover($user, $permission);
                if ($permission->status !== $expectedStatus) {
                    $errors[] = "La solicitud #{$permissionId} ya no está pendiente";
                    continue;
                }

                try {
                    if ($action === 'approve') {
                        $result = $this->permissionService->approve($permission, $user, $comments);
                    } else {
                        $result = $this->permissionService->reject($permission, $user, $comments);
                    }
                    
                    if ($result) {
                        $processedCount++;
                    }
                } catch (\Exception $e) {
                    $errors[] = "Error en solicitud #{$permissionId}: " . $e->getMessage();
                }
            }

            DB::commit();

            $actionText = $action === 'approve' ? 'aprobaron' : 'rechazaron';
            $message = "Se {$actionText} {$processedCount} solicitudes exitosamente.";
            
            if (!empty($errors)) {
                $message .= ' Errores: ' . implode('; ', $errors);
            }

            return redirect()->route('approvals.index')
                ->with($processedCount > 0 ? 'success' : 'warning', $message);
                
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('approvals.index')
                ->with('error', 'Error al procesar las solicitudes: ' . $e->getMessage());
        }
    }

    /**
     * Show statistics and reports (for HR and Admin)
     */
    public function reports(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('jefe_rrhh') && !$user->hasRole('admin')) {
            abort(403, 'No tiene permisos para ver reportes.');
        }

        $request->validate([
            'year' => 'nullable|integer|min:2020|max:' . (now()->year + 1),
            'month' => 'nullable|integer|min:1|max:12',
            'department_id' => 'nullable|exists:departments,id',
            'permission_type_id' => 'nullable|exists:permission_types,id',
        ]);

        $filters = [
            'year' => $request->get('year', now()->year),
            'month' => $request->get('month'),
            'department_id' => $request->get('department_id'),
            'permission_type_id' => $request->get('permission_type_id'),
        ];

        // Estadísticas generales
        $stats = $this->generateReportStats($filters);

        // Gráficos y datos adicionales
        $chartData = $this->generateChartData($filters);

        // Datos para filtros
        $departments = \App\Models\Department::all(['id', 'name']);
        $permissionTypes = \App\Models\PermissionType::active()->get(['id', 'name']);

        return view('approvals.reports', compact(
            'stats', 
            'chartData', 
            'filters', 
            'departments', 
            'permissionTypes'
        ));
    }

    /**
     * Export report data
     */
    public function exportReport(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->hasRole('jefe_rrhh') && !$user->hasRole('admin')) {
            abort(403, 'No tiene permisos para exportar reportes.');
        }

        $request->validate([
            'format' => 'required|in:excel,pdf,csv',
            'year' => 'required|integer',
            'month' => 'nullable|integer|min:1|max:12',
            'department_id' => 'nullable|exists:departments,id',
        ]);

        // Aquí implementarías la lógica de exportación
        // Por ejemplo, usando Laravel Excel o similar
        
        return response()->json([
            'message' => 'Exportación iniciada. Recibirá el archivo por correo electrónico.',
            'format' => $request->format
        ]);
    }

    /**
     * Get approval statistics (AJAX)
     */
    public function getStats(): JsonResponse
    {
        $user = Auth::user();
        $stats = $this->getApprovalStats($user);

        return response()->json($stats);
    }

    /**
     * Get detailed permission request info (AJAX)
     */
    public function getPermissionDetails(PermissionRequest $permission): JsonResponse
    {
        $user = Auth::user();
        
        if (!$user->canApprove($permission)) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        $permission->load(['user', 'permissionType', 'documents']);

        return response()->json([
            'permission' => $permission,
            'user_stats' => $this->permissionService->getUserPermissionStats(
                $permission->user,
                now()->year,
                now()->month
            )
        ]);
    }

    /**
     * Get pending approvals for a user with filters
     */
    private function getPendingApprovals($user, $status = 'pending', $departmentFilter = null, $permissionTypeFilter = null)
    {
        $query = PermissionRequest::with(['user.department', 'permissionType', 'approvals']);

        // Filtro por estado
        if ($status === 'pending') {
            if ($user->hasRole('jefe_inmediato') && !$user->hasRole('jefe_rrhh')) {
                // Solo solicitudes de subordinados directos
                $subordinateIds = $user->subordinates->pluck('id');
                $query->whereIn('user_id', $subordinateIds)
                      ->where('status', PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS)
                      // Excluir las que tienen skip_immediate_supervisor
                      ->where(function($q) {
                          $q->whereNull('metadata->skip_immediate_supervisor')
                            ->orWhereJsonContains('metadata->skip_immediate_supervisor', false);
                      });
            } elseif ($user->hasRole('jefe_rrhh')) {
                // Solicitudes pendientes de RRHH:
                // 1. En estado pending_hr (nivel 2 normal)
                // 2. En estado pending_immediate_boss CON skip_immediate_supervisor (nivel 1 especial)
                $query->where(function($q) {
                    $q->where('status', PermissionRequest::STATUS_PENDING_HR)
                      ->orWhere(function($subQ) {
                          $subQ->where('status', PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS)
                               ->whereJsonContains('metadata->skip_immediate_supervisor', true);
                      });
                });
            } elseif ($user->hasRole('admin')) {
                // Admin puede ver todas las pendientes
                $query->whereIn('status', [
                    PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS,
                    PermissionRequest::STATUS_PENDING_HR
                ]);
            } else {
                // Usuario normal no debería ver nada
                $query->whereRaw('1 = 0');
            }
        } else {
            // Historial de aprobaciones del usuario
            $query->whereHas('approvals', function ($q) use ($user, $status) {
                $q->where('approver_id', $user->id);
                if ($status !== 'all') {
                    $q->where('status', $status);
                }
            });
        }

        // Filtro por departamento
        if ($departmentFilter) {
            $query->whereHas('user', function ($q) use ($departmentFilter) {
                $q->where('department_id', $departmentFilter);
            });
        }

        // Filtro por tipo de permiso
        if ($permissionTypeFilter) {
            $query->where('permission_type_id', $permissionTypeFilter);
        }

        return $query->latest('submitted_at')->paginate(15);
    }

    /**
     * Get expected status for approver
     */
    private function getExpectedStatusForApprover($user, $permission): string
    {
        // Cast a int para compatibilidad PHP 8.3
        if ((int)$permission->user->immediate_supervisor_id === (int)$user->id) {
            return PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS;
        }

        if ($user->hasRole('jefe_rrhh')) {
            // Verificar si es caso especial (jefe inmediato no disponible)
            $skipImmediateSupervisor = $permission->metadata['skip_immediate_supervisor'] ?? false;

            if ($skipImmediateSupervisor && $permission->current_approval_level === 1) {
                // Caso especial: RRHH aprueba nivel 1
                return PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS;
            }

            // Caso normal: RRHH aprueba nivel 2
            return PermissionRequest::STATUS_PENDING_HR;
        }

        if ($user->hasRole('admin')) {
            return $permission->status; // Admin puede aprobar en cualquier estado
        }

        return '';
    }

    /**
     * Get approval statistics for dashboard
     */
    private function getApprovalStats($user): array
    {
        $baseQuery = PermissionRequest::query();

        // Filtrar según el rol del usuario
        if ($user->hasRole('jefe_inmediato') && !$user->hasRole('jefe_rrhh')) {
            $subordinateIds = $user->subordinates->pluck('id');
            $baseQuery->whereIn('user_id', $subordinateIds);
        } elseif (!$user->hasRole('jefe_rrhh') && !$user->hasRole('admin')) {
            $baseQuery->whereRaw('1 = 0'); // No mostrar nada si no tiene permisos
        }

        return [
            'pending_immediate' => (clone $baseQuery)
                ->where('status', PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS)
                ->count(),
            'pending_hr' => $user->hasRole('jefe_rrhh') || $user->hasRole('admin') 
                ? PermissionRequest::where('status', PermissionRequest::STATUS_PENDING_HR)->count()
                : 0,
            'approved_today' => $user->approvals()
                ->where('status', Approval::STATUS_APPROVED)
                ->whereDate('approved_at', today())
                ->count(),
            'rejected_today' => $user->approvals()
                ->where('status', Approval::STATUS_REJECTED)
                ->whereDate('approved_at', today())
                ->count(),
            'approved_this_month' => $user->approvals()
                ->where('status', Approval::STATUS_APPROVED)
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
            'rejected_this_month' => $user->approvals()
                ->where('status', Approval::STATUS_REJECTED)
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get departments for filter dropdown
     */
    private function getDepartmentsForFilter($user)
    {
        if ($user->hasRole('admin') || $user->hasRole('jefe_rrhh')) {
            return \App\Models\Department::all(['id', 'name']);
        }
        
        // Si es jefe inmediato, solo su departamento y subordinados
        $departmentIds = collect([$user->department_id]);
        if ($user->subordinates->count() > 0) {
            $subordinateDepts = $user->subordinates->pluck('department_id')->unique();
            $departmentIds = $departmentIds->merge($subordinateDepts);
        }
        
        return \App\Models\Department::whereIn('id', $departmentIds)->get(['id', 'name']);
    }

    /**
     * Generate report statistics
     */
    private function generateReportStats($filters): array
    {
        $query = PermissionRequest::query();

        if ($filters['year']) {
            $query->whereYear('created_at', $filters['year']);
        }
        if ($filters['month']) {
            $query->whereMonth('created_at', $filters['month']);
        }
        if ($filters['department_id']) {
            $query->whereHas('user', fn($q) => $q->where('department_id', $filters['department_id']));
        }
        if ($filters['permission_type_id']) {
            $query->where('permission_type_id', $filters['permission_type_id']);
        }

        return [
            'total_requests' => (clone $query)->count(),
            'approved' => (clone $query)->where('status', PermissionRequest::STATUS_APPROVED)->count(),
            'rejected' => (clone $query)->where('status', PermissionRequest::STATUS_REJECTED)->count(),
            'pending' => (clone $query)->whereIn('status', [
                PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS,
                PermissionRequest::STATUS_PENDING_HR
            ])->count(),
            'cancelled' => (clone $query)->where('status', PermissionRequest::STATUS_CANCELLED)->count(),
        ];
    }

    /**
     * Generate chart data for reports
     */
    private function generateChartData($filters): array
    {
        // Por tipo de permiso
        $byType = DB::table('permission_requests')
            ->join('permission_types', 'permission_requests.permission_type_id', '=', 'permission_types.id')
            ->select('permission_types.name', DB::raw('count(*) as total'))
            ->when($filters['year'], fn($q) => $q->whereYear('permission_requests.created_at', $filters['year']))
            ->when($filters['month'], fn($q) => $q->whereMonth('permission_requests.created_at', $filters['month']))
            ->groupBy('permission_types.name')
            ->get();

        // Por departamento
        $byDepartment = DB::table('permission_requests')
            ->join('users', 'permission_requests.user_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('count(*) as total'))
            ->when($filters['year'], fn($q) => $q->whereYear('permission_requests.created_at', $filters['year']))
            ->when($filters['month'], fn($q) => $q->whereMonth('permission_requests.created_at', $filters['month']))
            ->groupBy('departments.name')
            ->get();

        // Timeline mensual (últimos 12 meses)
        $timeline = DB::table('permission_requests')
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        return [
            'by_type' => $byType,
            'by_department' => $byDepartment,
            'timeline' => $timeline,
        ];
    }
}