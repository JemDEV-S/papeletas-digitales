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
        
        // 1. Obtener permisos propios del usuario (Paginado)
        // Usamos 'own_page' para que no interfiera con la otra tabla
        $ownTrackings = PermissionTracking::with(['permissionRequest.user', 'registeredByUser'])
            ->whereHas('permissionRequest', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20, ['*'], 'own_page');

        // 2. Obtener permisos del equipo según el rol (Paginado)
        // Inicializamos como colección vacía por si no tiene rol
        $teamTrackings = collect();
        
        if ($user->hasRole('jefe_inmediato')) {
            // Si es jefe inmediato, ver los permisos de sus subordinados
            $subordinateIds = $user->subordinates()->pluck('id');
            
            $teamTrackings = PermissionTracking::with(['permissionRequest.user', 'registeredByUser'])
                ->whereHas('permissionRequest', function($q) use ($subordinateIds) {
                    $q->whereIn('user_id', $subordinateIds);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20, ['*'], 'team_page');

        } elseif ($user->hasRole(['jefe_rrhh', 'admin'])) {
            // Si es jefe de RRHH o admin, ver todos los permisos excepto los propios
            $teamTrackings = PermissionTracking::with(['permissionRequest.user', 'registeredByUser'])
                ->whereHas('permissionRequest', function($q) use ($user) {
                    $q->where('user_id', '!=', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->paginate(20, ['*'], 'team_page');
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
            'departure_datetime' => 'nullable|date_format:Y-m-d H:i:s|before_or_equal:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $tracking = PermissionTracking::findOrFail($request->tracking_id);

        if ($tracking->registerDeparture(Auth::user(), $request->notes, $request->departure_datetime)) {
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
            'return_datetime' => 'nullable|date_format:Y-m-d H:i:s|before_or_equal:now',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $tracking = PermissionTracking::findOrFail($request->tracking_id);

        if ($tracking->registerReturn(Auth::user(), $request->notes, $request->return_datetime)) {

            // Update permission request status to completed
            $tracking->permissionRequest->update([
                'status' => PermissionRequest::STATUS_APPROVED
            ]);

            // Generar PDF con overlay de tracking
            try {
                $pdfService = app(\App\Services\PdfGeneratorService::class);
                $result = $pdfService->addTrackingOverlay($tracking->permissionRequest);

                if ($result['success']) {
                    \Log::info('PDF con tracking generado', [
                        'permission_id' => $tracking->permissionRequest->id,
                        'pdf_path' => $result['pdf_path']
                    ]);
                } else {
                    \Log::warning('No se pudo generar PDF con tracking', [
                        'permission_id' => $tracking->permissionRequest->id,
                        'error' => $result['message']
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al generar PDF con tracking: ' . $e->getMessage());
            }

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

    public function updateDeparture(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tracking_id' => 'required|exists:permission_trackings,id',
            'departure_datetime' => 'required|date_format:Y-m-d H:i:s',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $tracking = PermissionTracking::findOrFail($request->tracking_id);

        // Validar que la fecha de salida sea antes del regreso si existe
        if ($tracking->return_datetime && $request->departure_datetime >= $tracking->return_datetime->format('Y-m-d H:i:s')) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha de salida debe ser anterior a la fecha de regreso'
            ], 422);
        }

        if ($tracking->updateDeparture($request->departure_datetime, $request->notes)) {
            return response()->json([
                'success' => true,
                'message' => 'Salida actualizada correctamente',
                'tracking' => [
                    'departure_datetime' => $tracking->departure_datetime->format('Y-m-d H:i:s'),
                    'actual_hours_used' => $tracking->actual_hours_used,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo actualizar la salida'
        ], 400);
    }

    public function updateReturn(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tracking_id' => 'required|exists:permission_trackings,id',
            'return_datetime' => 'required|date_format:Y-m-d H:i:s',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $tracking = PermissionTracking::findOrFail($request->tracking_id);

        // Validar que la fecha de regreso sea después de la salida
        if ($tracking->departure_datetime && $request->return_datetime <= $tracking->departure_datetime->format('Y-m-d H:i:s')) {
            return response()->json([
                'success' => false,
                'message' => 'La fecha de regreso debe ser posterior a la fecha de salida'
            ], 422);
        }

        if ($tracking->updateReturn($request->return_datetime, $request->notes)) {
            // Regenerar PDF con los datos actualizados
            try {
                $pdfService = app(\App\Services\PdfGeneratorService::class);
                $result = $pdfService->addTrackingOverlay($tracking->permissionRequest);

                if ($result['success']) {
                    \Log::info('PDF con tracking actualizado', [
                        'permission_id' => $tracking->permissionRequest->id,
                        'pdf_path' => $result['pdf_path']
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Error al regenerar PDF con tracking: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Regreso actualizado correctamente',
                'tracking' => [
                    'return_datetime' => $tracking->return_datetime->format('Y-m-d H:i:s'),
                    'actual_hours_used' => $tracking->actual_hours_used,
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No se pudo actualizar el regreso'
        ], 400);
    }
}