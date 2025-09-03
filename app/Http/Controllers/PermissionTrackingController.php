<?php

namespace App\Http\Controllers;

use App\Models\PermissionTracking;
use App\Models\PermissionRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PermissionTrackingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Obtener permisos propios del usuario
        $ownTrackings = PermissionTracking::with(['permissionRequest.user', 'registeredByUser'])
            ->whereHas('permissionRequest', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Obtener permisos del equipo según el rol
        $teamTrackings = collect();
        
        if ($user->hasRole('jefe_inmediato')) {
            // Si es jefe inmediato, ver los permisos de sus subordinados
            $subordinateIds = $user->subordinates()->pluck('id');
            $teamTrackings = PermissionTracking::with(['permissionRequest.user', 'registeredByUser'])
                ->whereHas('permissionRequest', function($q) use ($subordinateIds) {
                    $q->whereIn('user_id', $subordinateIds);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif ($user->hasRole(['jefe_rrhh', 'admin'])) {
            // Si es jefe de RRHH o admin, ver todos los permisos excepto los propios
            $teamTrackings = PermissionTracking::with(['permissionRequest.user', 'registeredByUser'])
                ->whereHas('permissionRequest', function($q) use ($user) {
                    $q->where('user_id', '!=', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('tracking.index', compact('ownTrackings', 'teamTrackings'));
    }

    public function show(PermissionTracking $tracking)
    {
        $tracking->load(['permissionRequest.user', 'registeredByUser']);
        
        return view('tracking.show', compact('tracking'));
    }

    public function hrDashboard()
{
    // Verificar que llegue hasta aquí
    \Log::info('=== hrDashboard iniciado ===');
    
    try {
        $pendingDepartures = PermissionTracking::pendingDeparture()
            ->with(['permissionRequest.user'])
            ->get();
        \Log::info('Pending departures obtenidas: ' . $pendingDepartures->count());

        $currentlyOut = PermissionTracking::currentlyOut()
            ->with(['permissionRequest.user'])
            ->get();
        \Log::info('Currently out obtenidas: ' . $currentlyOut->count());

        $overdue = PermissionTracking::overdue()
            ->with(['permissionRequest.user'])
            ->get();
        \Log::info('Overdue obtenidas: ' . $overdue->count());

        \Log::info('Datos obtenidos correctamente, intentando cargar vista...');
        
        // Intentar retornar la vista
        return view('tracking.hr-dashboard', compact('pendingDepartures', 'currentlyOut', 'overdue'));
        
    } catch (\Exception $e) {
        \Log::error('Error en hrDashboard: ' . $e->getMessage());
        dd('Error: ' . $e->getMessage());
    }
}

    public function scanDni(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'dni' => 'required|string|size:8|regex:/^[0-9]+$/',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'DNI inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $dni = $request->dni;

        // Buscar el tracking activo para este DNI
        $tracking = PermissionTracking::forDni($dni)
            ->whereIn('tracking_status', [
                PermissionTracking::STATUS_PENDING,
                PermissionTracking::STATUS_OUT
            ])
            ->with(['permissionRequest.user'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$tracking) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró un permiso activo para este DNI'
            ], 404);
        }

        $employee = $tracking->permissionRequest->user;
        
        return response()->json([
            'success' => true,
            'tracking' => [
                'id' => $tracking->id,
                'status' => $tracking->tracking_status,
                'status_label' => $tracking->getStatusLabel(),
                'permission_type' => $tracking->permissionRequest->permissionType->name ?? 'N/A',
                'requested_hours' => $tracking->permissionRequest->requested_hours,
                'departure_datetime' => $tracking->departure_datetime,
                'return_datetime' => $tracking->return_datetime,
                'actual_hours_used' => $tracking->actual_hours_used,
                'is_overdue' => $tracking->isOverdue(),
            ],
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->name,
                'dni' => $employee->dni,
                'email' => $employee->email,
            ],
            'action_needed' => $tracking->tracking_status === PermissionTracking::STATUS_PENDING ? 'departure' : 'return'
        ]);
    }

    public function registerDeparture(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tracking_id' => 'required|exists:permission_trackings,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $tracking = PermissionTracking::findOrFail($request->tracking_id);
        
        if ($tracking->registerDeparture(Auth::user(), $request->notes)) {
            // Update permission request status to in_progress
            $tracking->permissionRequest->update([
                'status' => PermissionRequest::STATUS_IN_PROGRESS
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Salida registrada correctamente',
                'tracking' => [
                    'status' => $tracking->tracking_status,
                    'departure_datetime' => $tracking->departure_datetime,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo registrar la salida'
        ], 400);
    }

    public function registerReturn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tracking_id' => 'required|exists:permission_trackings,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $tracking = PermissionTracking::findOrFail($request->tracking_id);
        
        if ($tracking->registerReturn(Auth::user(), $request->notes)) {

            // Update permission request status to completed
            $tracking->permissionRequest->update([
                'status' => PermissionRequest::STATUS_APPROVED
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Regreso registrado correctamente',
                'tracking' => [
                    'status' => $tracking->tracking_status,
                    'return_datetime' => $tracking->return_datetime,
                    'actual_hours_used' => $tracking->actual_hours_used,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo registrar el regreso'
        ], 400);
    }

    public function markOverdue()
    {
        $overdueCount = 0;
        
        $currentlyOut = PermissionTracking::currentlyOut()->get();
        
        foreach ($currentlyOut as $tracking) {
            if ($tracking->markAsOverdue()) {
                $overdueCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Se marcaron {$overdueCount} registros como retrasados",
            'overdue_count' => $overdueCount
        ]);
    }

    public function getActiveTrackings(): JsonResponse
    {
        $trackings = PermissionTracking::whereIn('tracking_status', [
                PermissionTracking::STATUS_PENDING,
                PermissionTracking::STATUS_OUT,
                PermissionTracking::STATUS_OVERDUE
            ])
            ->with(['permissionRequest.user', 'permissionRequest.permissionType'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($tracking) {
                return [
                    'id' => $tracking->id,
                    'employee_name' => $tracking->permissionRequest->user->name,
                    'employee_dni' => $tracking->employee_dni,
                    'permission_type' => $tracking->permissionRequest->permissionType->name ?? 'N/A',
                    'status' => $tracking->tracking_status,
                    'status_label' => $tracking->getStatusLabel(),
                    'status_color' => $tracking->getStatusColor(),
                    'departure_datetime' => $tracking->departure_datetime,
                    'return_datetime' => $tracking->return_datetime,
                    'is_overdue' => $tracking->isOverdue(),
                    'requested_hours' => $tracking->permissionRequest->requested_hours,
                    'actual_hours_used' => $tracking->actual_hours_used,
                ];
            });

        return response()->json([
            'success' => true,
            'trackings' => $trackings
        ]);
    }
}
