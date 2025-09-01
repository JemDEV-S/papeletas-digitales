<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        $data = [];

        // Datos comunes para todos los usuarios
        $data['user'] = $user;
        $data['myRequests'] = $user->permissionRequests()
            ->with(['permissionType', 'approvals'])
            ->latest()
            ->take(5)
            ->get();

        // Estadísticas de mis solicitudes
        $data['myStats'] = [
            'total' => $user->permissionRequests()->count(),
            'pending' => $user->permissionRequests()->pending()->count(),
            'approved' => $user->permissionRequests()->approved()->count(),
            'rejected' => $user->permissionRequests()->rejected()->count(),
        ];

        // Datos específicos según el rol
        if ($user->hasRole('admin')) {
            $data = array_merge($data, $this->getAdminData());
        } elseif ($user->hasRole('jefe_rrhh')) {
            $data = array_merge($data, $this->getHRData());
        } elseif ($user->hasRole('jefe_inmediato')) {
            $data = array_merge($data, $this->getSupervisorData($user));
        }

        // Solicitudes pendientes de aprobación para este usuario
        if ($user->canApprove(new PermissionRequest())) {
            $data['pendingApprovals'] = $this->getPendingApprovals($user);
        }

        return view('dashboard.index', $data);
    }

    /**
     * Get admin specific data
     */
    private function getAdminData(): array
    {
        return [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'totalRequests' => PermissionRequest::count(),
            'requestsByStatus' => PermissionRequest::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray(),
            'recentRequests' => PermissionRequest::with(['user', 'permissionType'])
                ->latest()
                ->take(10)
                ->get(),
        ];
    }

    /**
     * Get HR specific data
     */
    private function getHRData(): array
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return [
            'pendingHRApprovals' => PermissionRequest::where('status', 'pending_hr')->count(),
            'monthlyRequests' => PermissionRequest::whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->count(),
            'requestsByType' => PermissionRequest::with('permissionType')
                ->whereYear('created_at', $currentYear)
                ->whereMonth('created_at', $currentMonth)
                ->get()
                ->groupBy('permissionType.name')
                ->map->count(),
            'departmentStats' => $this->getDepartmentStats(),
        ];
    }

    /**
     * Get supervisor specific data
     */
    private function getSupervisorData(User $supervisor): array
    {
        $subordinateIds = $supervisor->subordinates->pluck('id');

        return [
            'subordinates' => $supervisor->subordinates()->with('department')->get(),
            'subordinateRequests' => PermissionRequest::whereIn('user_id', $subordinateIds)
                ->with(['user', 'permissionType'])
                ->latest()
                ->take(10)
                ->get(),
            'pendingSubordinateApprovals' => PermissionRequest::whereIn('user_id', $subordinateIds)
                ->where('status', 'pending_immediate_boss')
                ->count(),
        ];
    }

    /**
     * Get pending approvals for a user
     */
    private function getPendingApprovals(User $user): object
    {
        $query = PermissionRequest::with(['user', 'permissionType', 'user.department']);

        if ($user->hasRole('jefe_inmediato') && !$user->hasRole('jefe_rrhh')) {
            // Solo solicitudes de subordinados directos
            $subordinateIds = $user->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds)
                  ->where('status', 'pending_immediate_boss');
        } elseif ($user->hasRole('jefe_rrhh')) {
            // Solicitudes pendientes de RRHH
            $query->where('status', 'pending_hr');
        } elseif ($user->hasRole('admin')) {
            // Admin puede ver todas las pendientes
            $query->whereIn('status', ['pending_immediate_boss', 'pending_hr']);
        }

        return $query->latest()->paginate(10);
    }

    /**
     * Get statistics by department
     */
    private function getDepartmentStats(): array
    {
        return DB::table('permission_requests')
            ->join('users', 'permission_requests.user_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('count(*) as total'))
            ->whereYear('permission_requests.created_at', now()->year)
            ->whereMonth('permission_requests.created_at', now()->month)
            ->groupBy('departments.name')
            ->pluck('total', 'name')
            ->toArray();
    }

    /**
     * Get dashboard statistics for AJAX updates
     */
    public function getStats(Request $request)
    {
        $user = Auth::user();
        
        // Estadísticas de mis solicitudes
        $stats = [
            'total' => $user->permissionRequests()->count(),
            'pending' => $user->permissionRequests()->pending()->count(),
            'approved' => $user->permissionRequests()->approved()->count(),
            'rejected' => $user->permissionRequests()->rejected()->count(),
        ];
        
        if ($request->expectsJson()) {
            return response()->json($stats);
        }
        
        return response()->json($stats);
    }

    /**
     * Get user's requests section for AJAX updates
     */
    public function getMyRequestsSection(Request $request)
    {
        $user = Auth::user();
        $myRequests = $user->permissionRequests()
            ->with(['permissionType', 'approvals'])
            ->latest()
            ->take(5)
            ->get();

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('dashboard.partials.my-requests', compact('myRequests'))->render()
            ]);
        }

        return view('dashboard.partials.my-requests', compact('myRequests'));
    }

    /**
     * Get team requests section for AJAX updates (for supervisors/admin)
     */
    public function getTeamRequestsSection(Request $request)
    {
        $user = Auth::user();
        $teamRequests = collect();
        
        if ($user->hasRole('admin')) {
            $teamRequests = PermissionRequest::with(['user', 'permissionType', 'user.department'])
                ->latest()
                ->take(10)
                ->get();
        } elseif ($user->hasRole('jefe_rrhh')) {
            $teamRequests = PermissionRequest::where('status', 'pending_hr')
                ->with(['user', 'permissionType', 'user.department'])
                ->latest()
                ->take(10)
                ->get();
        } elseif ($user->hasRole('jefe_inmediato')) {
            $subordinateIds = $user->subordinates->pluck('id');
            $teamRequests = PermissionRequest::whereIn('user_id', $subordinateIds)
                ->with(['user', 'permissionType', 'user.department'])
                ->latest()
                ->take(10)
                ->get();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'html' => view('dashboard.partials.team-requests', compact('teamRequests'))->render()
            ]);
        }

        return view('dashboard.partials.team-requests', compact('teamRequests'));
    }
}