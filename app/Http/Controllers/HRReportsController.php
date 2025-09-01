<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionRequest;
use App\Models\User;
use App\Models\Department;
use App\Models\PermissionType;
use App\Models\PermissionTracking;
use App\Models\Approval;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReportExport;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HRReportsController extends Controller
{
    public function __construct()
    {
        // El middleware de autenticación y roles se maneja en las rutas
    }

    public function dashboard()
    {
        $stats = $this->getDashboardStats();
        return view('hr.reports.dashboard', compact('stats'));
    }

    public function requestsByStatus(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = PermissionRequest::whereBetween('created_at', [$dateFrom, $dateTo])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $chartData = [
            'labels' => $data->map(fn($item) => ucfirst(str_replace('_', ' ', $item->status))),
            'data' => $data->pluck('total'),
            'colors' => $this->getStatusColors($data->pluck('status'))
        ];

        if ($request->wantsJson()) {
            return response()->json($chartData);
        }

        return view('hr.reports.requests-by-status', compact('data', 'chartData', 'dateFrom', 'dateTo'));
    }

    public function requestsByType(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = PermissionRequest::join('permission_types', 'permission_requests.permission_type_id', '=', 'permission_types.id')
            ->whereBetween('permission_requests.created_at', [$dateFrom, $dateTo])
            ->select('permission_types.name', 'permission_types.code', DB::raw('count(*) as total'))
            ->groupBy('permission_types.id', 'permission_types.name', 'permission_types.code')
            ->orderBy('total', 'desc')
            ->get();

        $chartData = [
            'labels' => $data->pluck('name'),
            'data' => $data->pluck('total'),
            'colors' => $this->generateColors($data->count())
        ];

        if ($request->wantsJson()) {
            return response()->json($chartData);
        }

        return view('hr.reports.requests-by-type', compact('data', 'chartData', 'dateFrom', 'dateTo'));
    }

    public function requestsByDepartment(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = PermissionRequest::join('users', 'permission_requests.user_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->whereBetween('permission_requests.created_at', [$dateFrom, $dateTo])
            ->select(
                'departments.name', 
                'departments.code',
                DB::raw('count(*) as total_requests'),
                DB::raw('sum(case when permission_requests.status = "approved" then 1 else 0 end) as approved'),
                DB::raw('sum(case when permission_requests.status = "rejected" then 1 else 0 end) as rejected'),
                DB::raw('sum(case when permission_requests.status like "pending%" then 1 else 0 end) as pending')
            )
            ->groupBy('departments.id', 'departments.name', 'departments.code')
            ->orderBy('total_requests', 'desc')
            ->get();

        $chartData = [
            'labels' => $data->pluck('name'),
            'data' => $data->pluck('total_requests'),
            'colors' => $this->generateColors($data->count())
        ];

        if ($request->wantsJson()) {
            return response()->json($chartData);
        }

        return view('hr.reports.requests-by-department', compact('data', 'chartData', 'dateFrom', 'dateTo'));
    }

    public function approvalTimes(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = PermissionRequest::with(['approvals' => function($query) {
                $query->where('status', 'approved')->orderBy('approval_level');
            }])
            ->whereBetween('submitted_at', [$dateFrom, $dateTo])
            ->where('status', 'approved')
            ->get()
            ->map(function ($request) {
                $submittedAt = $request->submitted_at;
                $finalApproval = $request->approvals->last();
                
                if (!$finalApproval || !$submittedAt) {
                    return null;
                }
                
                $hoursToApproval = $submittedAt->diffInHours($finalApproval->approved_at);
                
                return [
                    'request_number' => $request->request_number,
                    'employee_name' => $request->user->full_name,
                    'permission_type' => $request->permissionType->name,
                    'submitted_at' => $submittedAt,
                    'approved_at' => $finalApproval->approved_at,
                    'hours_to_approval' => $hoursToApproval,
                    'days_to_approval' => round($hoursToApproval / 24, 1)
                ];
            })
            ->filter()
            ->sortBy('hours_to_approval');

        $avgApprovalTime = $data->avg('hours_to_approval');
        $medianApprovalTime = $data->median('hours_to_approval');
        
        $chartData = [
            'labels' => ['0-24h', '24-48h', '48-72h', '72h+'],
            'data' => [
                $data->where('hours_to_approval', '<=', 24)->count(),
                $data->whereBetween('hours_to_approval', [24, 48])->count(),
                $data->whereBetween('hours_to_approval', [48, 72])->count(),
                $data->where('hours_to_approval', '>', 72)->count(),
            ],
            'colors' => ['#22c55e', '#eab308', '#f97316', '#ef4444']
        ];

        if ($request->wantsJson()) {
            return response()->json([
                'chartData' => $chartData,
                'avgApprovalTime' => round($avgApprovalTime, 1),
                'medianApprovalTime' => round($medianApprovalTime, 1)
            ]);
        }

        return view('hr.reports.approval-times', compact('data', 'chartData', 'avgApprovalTime', 'medianApprovalTime', 'dateFrom', 'dateTo'));
    }

    public function absenteeism(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = PermissionTracking::join('permission_requests', 'permission_trackings.permission_request_id', '=', 'permission_requests.id')
            ->join('users', 'permission_requests.user_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->join('permission_types', 'permission_requests.permission_type_id', '=', 'permission_types.id')
            ->whereBetween('permission_trackings.created_at', [$dateFrom, $dateTo])
            ->select(
                'users.name as employee_name',
                'users.dni',
                'departments.name as department',
                'permission_types.name as permission_type',
                'permission_trackings.actual_hours_used',
                'permission_trackings.tracking_status',
                'permission_trackings.departure_datetime',
                'permission_trackings.return_datetime'
            )
            ->get();

        $summary = [
            'total_hours_used' => $data->sum('actual_hours_used'),
            'total_permissions' => $data->count(),
            'avg_hours_per_permission' => $data->avg('actual_hours_used'),
            'by_department' => $data->groupBy('department')->map(function ($dept) {
                return [
                    'total_hours' => $dept->sum('actual_hours_used'),
                    'total_permissions' => $dept->count(),
                    'avg_hours' => $dept->avg('actual_hours_used')
                ];
            })
        ];

        $chartData = [
            'labels' => $summary['by_department']->keys(),
            'data' => $summary['by_department']->pluck('total_hours'),
            'colors' => $this->generateColors($summary['by_department']->count())
        ];

        if ($request->wantsJson()) {
            return response()->json(['summary' => $summary, 'chartData' => $chartData]);
        }

        return view('hr.reports.absenteeism', compact('data', 'summary', 'chartData', 'dateFrom', 'dateTo'));
    }

    public function activeEmployees(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = User::with('department')
            ->withCount(['permissionRequests' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->having('permission_requests_count', '>', 0)
            ->orderBy('permission_requests_count', 'desc')
            ->take(20)
            ->get();

        $chartData = [
            'labels' => $data->pluck('full_name'),
            'data' => $data->pluck('permission_requests_count'),
            'colors' => $this->generateColors($data->count())
        ];

        if ($request->wantsJson()) {
            return response()->json($chartData);
        }

        return view('hr.reports.active-employees', compact('data', 'chartData', 'dateFrom', 'dateTo'));
    }

    public function supervisorPerformance(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = User::whereHas('role', function ($query) {
                $query->whereIn('name', ['jefe_inmediato', 'jefe_rrhh']);
            })
            ->with(['approvals' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->get()
            ->map(function ($supervisor) {
                $approvals = $supervisor->approvals;
                $avgTimeToApproval = $approvals->where('status', 'approved')->avg(function ($approval) {
                    return $approval->created_at->diffInHours($approval->approved_at ?? now());
                });

                return [
                    'name' => $supervisor->full_name,
                    'department' => $supervisor->department->name ?? 'N/A',
                    'total_approvals' => $approvals->count(),
                    'approved' => $approvals->where('status', 'approved')->count(),
                    'rejected' => $approvals->where('status', 'rejected')->count(),
                    'pending' => $approvals->where('status', 'pending')->count(),
                    'approval_rate' => $approvals->count() > 0 ? round(($approvals->where('status', 'approved')->count() / $approvals->count()) * 100, 1) : 0,
                    'avg_approval_time_hours' => round($avgTimeToApproval ?? 0, 1)
                ];
            })
            ->filter(function ($supervisor) {
                return $supervisor['total_approvals'] > 0;
            })
            ->sortBy('avg_approval_time_hours');

        if ($request->wantsJson()) {
            return response()->json($data->values());
        }

        return view('hr.reports.supervisor-performance', compact('data', 'dateFrom', 'dateTo'));
    }

    public function realTimeTracking(Request $request)
    {
        $currentlyOut = PermissionTracking::currentlyOut()
            ->with(['permissionRequest.user.department', 'permissionRequest.permissionType'])
            ->get()
            ->map(function ($tracking) {
                $minutesOut = $tracking->departure_datetime ? 
                    $tracking->departure_datetime->diffInMinutes(now()) : 0;
                
                return [
                    'employee_name' => $tracking->permissionRequest->user->full_name,
                    'dni' => $tracking->employee_dni,
                    'department' => $tracking->permissionRequest->user->department->name ?? 'N/A',
                    'permission_type' => $tracking->permissionRequest->permissionType->name,
                    'departure_time' => $tracking->departure_datetime,
                    'minutes_out' => $minutesOut,
                    'hours_out' => round($minutesOut / 60, 1),
                    'status' => $tracking->getStatusLabel(),
                    'is_overdue' => $tracking->isOverdue()
                ];
            });

        $overdue = $currentlyOut->where('is_overdue', true);
        
        if ($request->wantsJson()) {
            return response()->json([
                'currently_out' => $currentlyOut,
                'overdue_count' => $overdue->count(),
                'total_out' => $currentlyOut->count()
            ]);
        }

        return view('hr.reports.real-time-tracking', compact('currentlyOut', 'overdue'));
    }

    public function temporalTrends(Request $request)
    {
        $year = $request->get('year', Carbon::now()->year);
        $type = $request->get('type', 'monthly'); // monthly, weekly, daily
        
        $query = PermissionRequest::whereYear('created_at', $year);
        
        switch ($type) {
            case 'monthly':
                $data = $query->select(
                        DB::raw('MONTH(created_at) as period'),
                        DB::raw('MONTHNAME(created_at) as period_label'),
                        DB::raw('COUNT(*) as total'),
                        DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                        DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
                    )
                    ->groupBy(DB::raw('MONTH(created_at)'), DB::raw('MONTHNAME(created_at)'))
                    ->orderBy('period')
                    ->get();
                break;
                
            case 'weekly':
                $data = $query->select(
                        DB::raw('WEEK(created_at) as period'),
                        DB::raw('CONCAT("Semana ", WEEK(created_at)) as period_label'),
                        DB::raw('COUNT(*) as total'),
                        DB::raw('SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) as approved'),
                        DB::raw('SUM(CASE WHEN status = "rejected" THEN 1 ELSE 0 END) as rejected')
                    )
                    ->groupBy(DB::raw('WEEK(created_at)'))
                    ->orderBy('period')
                    ->get();
                break;
                
            default:
                $data = collect();
        }

        $chartData = [
            'labels' => $data->pluck('period_label'),
            'datasets' => [
                [
                    'label' => 'Total Solicitudes',
                    'data' => $data->pluck('total'),
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)'
                ],
                [
                    'label' => 'Aprobadas',
                    'data' => $data->pluck('approved'),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)'
                ],
                [
                    'label' => 'Rechazadas',
                    'data' => $data->pluck('rejected'),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)'
                ]
            ]
        ];

        if ($request->wantsJson()) {
            return response()->json($chartData);
        }

        return view('hr.reports.temporal-trends', compact('data', 'chartData', 'year', 'type'));
    }

    public function compliance(Request $request)
    {
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $permissionTypes = PermissionType::with(['permissionRequests' => function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('created_at', [$dateFrom, $dateTo])
                  ->where('status', 'approved');
        }])->get();

        $data = $permissionTypes->map(function ($type) {
            $requests = $type->permissionRequests;
            $violations = 0;
            $details = [];

            foreach ($requests->groupBy('user_id') as $userId => $userRequests) {
                $user = $userRequests->first()->user;
                $totalHours = $userRequests->sum(function ($req) {
                    return $req->tracking ? $req->tracking->actual_hours_used ?? 0 : 0;
                });
                $totalTimes = $userRequests->count();

                // Check monthly hour limit
                if ($type->hasMonthlyLimit() && $totalHours > $type->max_hours_per_month) {
                    $violations++;
                    $details[] = [
                        'employee' => $user->full_name,
                        'type' => 'Límite mensual de horas excedido',
                        'limit' => $type->max_hours_per_month,
                        'actual' => $totalHours
                    ];
                }

                // Check frequency limit
                if ($type->hasFrequencyLimit() && $totalTimes > $type->max_times_per_month) {
                    $violations++;
                    $details[] = [
                        'employee' => $user->full_name,
                        'type' => 'Límite mensual de veces excedido',
                        'limit' => $type->max_times_per_month,
                        'actual' => $totalTimes
                    ];
                }
            }

            return [
                'permission_type' => $type->name,
                'total_requests' => $requests->count(),
                'violations' => $violations,
                'compliance_rate' => $requests->count() > 0 ? round((($requests->count() - $violations) / $requests->count()) * 100, 1) : 100,
                'violation_details' => $details
            ];
        });

        if ($request->wantsJson()) {
            return response()->json($data);
        }

        return view('hr.reports.compliance', compact('data', 'dateFrom', 'dateTo'));
    }

    public function export(Request $request)
    {
        $reportType = $request->get('type', 'requests_by_status');
        $dateFrom = $request->get('date_from', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $dateTo = $request->get('date_to', Carbon::now()->endOfMonth()->format('Y-m-d'));
        
        $data = $this->getReportData($reportType, $dateFrom, $dateTo);
        $filename = "reporte_{$reportType}_" . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new ReportExport($data, $reportType), $filename);
    }

    private function getDashboardStats()
    {
        $currentMonth = Carbon::now()->startOfMonth();
        $currentYear = Carbon::now()->startOfYear();

        return [
            'total_requests_month' => PermissionRequest::where('created_at', '>=', $currentMonth)->count(),
            'pending_requests' => PermissionRequest::pending()->count(),
            'approved_requests_month' => PermissionRequest::where('created_at', '>=', $currentMonth)
                ->where('status', 'approved')->count(),
            'rejected_requests_month' => PermissionRequest::where('created_at', '>=', $currentMonth)
                ->where('status', 'rejected')->count(),
            'currently_out' => PermissionTracking::currentlyOut()->count(),
            'overdue_returns' => PermissionTracking::overdue()->count(),
            'avg_approval_time' => $this->getAvgApprovalTime(),
            'top_permission_types' => $this->getTopPermissionTypes(),
            'department_activity' => $this->getDepartmentActivity()
        ];
    }

    private function getAvgApprovalTime()
    {
        return PermissionRequest::with(['approvals' => function($query) {
                $query->where('status', 'approved')->orderBy('approval_level');
            }])
            ->where('status', 'approved')
            ->whereMonth('created_at', Carbon::now()->month)
            ->get()
            ->map(function ($request) {
                $finalApproval = $request->approvals->last();
                return $request->submitted_at && $finalApproval ? 
                    $request->submitted_at->diffInHours($finalApproval->approved_at) : null;
            })
            ->filter()
            ->avg();
    }

    private function getTopPermissionTypes()
    {
        return PermissionRequest::join('permission_types', 'permission_requests.permission_type_id', '=', 'permission_types.id')
            ->whereMonth('permission_requests.created_at', Carbon::now()->month)
            ->select('permission_types.name', DB::raw('count(*) as total'))
            ->groupBy('permission_types.id', 'permission_types.name')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();
    }

    private function getDepartmentActivity()
    {
        return Department::withCount(['users as requests_count' => function ($query) {
                $query->join('permission_requests', 'users.id', '=', 'permission_requests.user_id')
                      ->whereMonth('permission_requests.created_at', Carbon::now()->month);
            }])
            ->having('requests_count', '>', 0)
            ->orderBy('requests_count', 'desc')
            ->take(5)
            ->get();
    }

    private function getStatusColors($statuses)
    {
        $colors = [];
        foreach ($statuses as $status) {
            $colors[] = match($status) {
                'approved' => '#22c55e',
                'rejected' => '#ef4444',
                'pending_immediate_boss' => '#eab308',
                'pending_hr' => '#f97316',
                'draft' => '#6b7280',
                'cancelled' => '#9ca3af',
                default => '#3b82f6'
            };
        }
        return $colors;
    }

    private function generateColors($count)
    {
        $baseColors = [
            '#3b82f6', '#ef4444', '#22c55e', '#eab308', '#8b5cf6',
            '#f97316', '#06b6d4', '#ec4899', '#84cc16', '#f59e0b'
        ];
        
        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }
        
        return $colors;
    }

    private function getReportData($reportType, $dateFrom, $dateTo)
    {
        return match($reportType) {
            'requests_by_status' => $this->getRequestsByStatusData($dateFrom, $dateTo),
            'requests_by_type' => $this->getRequestsByTypeData($dateFrom, $dateTo),
            'requests_by_department' => $this->getRequestsByDepartmentData($dateFrom, $dateTo),
            'absenteeism' => $this->getAbsenteeismData($dateFrom, $dateTo),
            'active_employees' => $this->getActiveEmployeesData($dateFrom, $dateTo),
            'supervisor_performance' => $this->getSupervisorPerformanceData($dateFrom, $dateTo),
            default => []
        };
    }

    private function getRequestsByStatusData($dateFrom, $dateTo)
    {
        return PermissionRequest::with(['user', 'permissionType'])
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->get()
            ->map(function ($request) {
                return [
                    'Número' => $request->request_number,
                    'Empleado' => $request->user->full_name,
                    'Tipo Permiso' => $request->permissionType->name,
                    'Estado' => $request->getStatusLabel(),
                    'Fecha Solicitud' => $request->created_at->format('d/m/Y H:i'),
                    'Fecha Envío' => $request->submitted_at ? $request->submitted_at->format('d/m/Y H:i') : 'N/A'
                ];
            })->toArray();
    }

    private function getRequestsByTypeData($dateFrom, $dateTo)
    {
        return PermissionRequest::join('permission_types', 'permission_requests.permission_type_id', '=', 'permission_types.id')
            ->join('users', 'permission_requests.user_id', '=', 'users.id')
            ->whereBetween('permission_requests.created_at', [$dateFrom, $dateTo])
            ->select(
                'permission_requests.request_number',
                'users.full_name as empleado',
                'permission_types.name as tipo_permiso',
                'permission_requests.status',
                'permission_requests.created_at'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'Número' => $item->request_number,
                    'Empleado' => $item->empleado,
                    'Tipo Permiso' => $item->tipo_permiso,
                    'Estado' => ucfirst(str_replace('_', ' ', $item->status)),
                    'Fecha' => Carbon::parse($item->created_at)->format('d/m/Y H:i')
                ];
            })->toArray();
    }

    private function getRequestsByDepartmentData($dateFrom, $dateTo)
    {
        return PermissionRequest::join('users', 'permission_requests.user_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->join('permission_types', 'permission_requests.permission_type_id', '=', 'permission_types.id')
            ->whereBetween('permission_requests.created_at', [$dateFrom, $dateTo])
            ->select(
                'permission_requests.request_number',
                'users.full_name as empleado',
                'departments.name as departamento',
                'permission_types.name as tipo_permiso',
                'permission_requests.status',
                'permission_requests.created_at'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'Número' => $item->request_number,
                    'Empleado' => $item->empleado,
                    'Departamento' => $item->departamento,
                    'Tipo Permiso' => $item->tipo_permiso,
                    'Estado' => ucfirst(str_replace('_', ' ', $item->status)),
                    'Fecha' => Carbon::parse($item->created_at)->format('d/m/Y H:i')
                ];
            })->toArray();
    }

    private function getAbsenteeismData($dateFrom, $dateTo)
    {
        return PermissionTracking::join('permission_requests', 'permission_trackings.permission_request_id', '=', 'permission_requests.id')
            ->join('users', 'permission_requests.user_id', '=', 'users.id')
            ->join('departments', 'users.department_id', '=', 'departments.id')
            ->join('permission_types', 'permission_requests.permission_type_id', '=', 'permission_types.id')
            ->whereBetween('permission_trackings.created_at', [$dateFrom, $dateTo])
            ->select(
                'users.full_name as empleado',
                'users.dni',
                'departments.name as departamento',
                'permission_types.name as tipo_permiso',
                'permission_trackings.actual_hours_used as horas_utilizadas',
                'permission_trackings.departure_datetime as salida',
                'permission_trackings.return_datetime as regreso',
                'permission_trackings.tracking_status as estado'
            )
            ->get()
            ->map(function ($item) {
                return [
                    'Empleado' => $item->empleado,
                    'DNI' => $item->dni,
                    'Departamento' => $item->departamento,
                    'Tipo Permiso' => $item->tipo_permiso,
                    'Horas Utilizadas' => $item->horas_utilizadas ?? 'N/A',
                    'Fecha/Hora Salida' => $item->salida ? Carbon::parse($item->salida)->format('d/m/Y H:i') : 'N/A',
                    'Fecha/Hora Regreso' => $item->regreso ? Carbon::parse($item->regreso)->format('d/m/Y H:i') : 'N/A',
                    'Estado' => ucfirst(str_replace('_', ' ', $item->estado))
                ];
            })->toArray();
    }

    private function getActiveEmployeesData($dateFrom, $dateTo)
    {
        return User::with('department')
            ->withCount(['permissionRequests' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->having('permission_requests_count', '>', 0)
            ->orderBy('permission_requests_count', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'Empleado' => $user->full_name,
                    'DNI' => $user->dni,
                    'Email' => $user->email,
                    'Departamento' => $user->department->name ?? 'N/A',
                    'Total Solicitudes' => $user->permission_requests_count
                ];
            })->toArray();
    }

    private function getSupervisorPerformanceData($dateFrom, $dateTo)
    {
        return User::whereHas('role', function ($query) {
                $query->whereIn('name', ['jefe_inmediato', 'jefe_rrhh']);
            })
            ->with(['approvals' => function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('created_at', [$dateFrom, $dateTo]);
            }])
            ->get()
            ->filter(function ($supervisor) {
                return $supervisor->approvals->count() > 0;
            })
            ->map(function ($supervisor) {
                $approvals = $supervisor->approvals;
                return [
                    'Supervisor' => $supervisor->full_name,
                    'Departamento' => $supervisor->department->name ?? 'N/A',
                    'Total Aprobaciones' => $approvals->count(),
                    'Aprobadas' => $approvals->where('status', 'approved')->count(),
                    'Rechazadas' => $approvals->where('status', 'rejected')->count(),
                    'Pendientes' => $approvals->where('status', 'pending')->count(),
                    'Tasa de Aprobación (%)' => $approvals->count() > 0 ? round(($approvals->where('status', 'approved')->count() / $approvals->count()) * 100, 1) : 0
                ];
            })->toArray();
    }
}