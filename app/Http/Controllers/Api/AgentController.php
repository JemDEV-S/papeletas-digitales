<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PermissionTracking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AgentController extends Controller
{
    // El middleware de autenticación se maneja a nivel de rutas

    public function ping(): JsonResponse
    {
        return response()->json([
            'status' => 'ok',
            'timestamp' => now()->toISOString(),
            'server' => config('app.name'),
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|integer|min:1|max:5',
            'agent_name' => 'required|string|max:255',
            'zkteco_ip' => 'required|ip',
            'status' => 'required|in:online,offline',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $agentData = $validator->validated();
            
            // Registrar/actualizar agente en configuración
            $this->updateAgentConfig($agentData);
            
            Log::info('Agente registrado', $agentData);

            return response()->json([
                'success' => true,
                'message' => 'Agente registrado exitosamente',
                'agent_id' => $agentData['agent_id'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar agente: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|integer|min:1|max:5',
            'status' => 'required|in:online,offline',
            'timestamp' => 'required|date',
            'stats' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Actualizar último heartbeat
            $this->updateAgentHeartbeat($data);

            return response()->json([
                'success' => true,
                'timestamp' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error en heartbeat: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function getEmployees(Request $request): JsonResponse
    {
        try {
            $employees = User::where('is_active', true)
                ->select(['id', 'dni', 'first_name', 'last_name', 'department_id'])
                ->with('department:id,name')
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'dni' => $user->dni,
                        'first_name' => $user->first_name,
                        'last_name' => $user->last_name,
                        'department' => $user->department?->name ?? '',
                        'is_active' => true,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $employees,
                'count' => $employees->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener empleados: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener empleados'
            ], 500);
        }
    }

    public function storeAccessEvent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'agent_id' => 'required|integer|min:1|max:5',
            'dni' => 'required|string|size:8',
            'event_type' => 'required|in:entry,exit',
            'event_datetime' => 'required|date',
            'zkteco_event_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $data = $validator->validated();
            
            // Buscar empleado por DNI
            $user = User::where('dni', $data['dni'])->where('is_active', true)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // Procesar el evento de acceso
            $result = $this->processAccessEvent($user, $data);

            if ($result['success']) {
                Log::info('Evento de acceso procesado', [
                    'dni' => $data['dni'],
                    'type' => $data['event_type'],
                    'datetime' => $data['event_datetime'],
                    'agent_id' => $data['agent_id']
                ]);

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'permission_tracking_id' => $result['tracking_id'] ?? null,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 422);
            }
        } catch (\Exception $e) {
            Log::error('Error al procesar evento de acceso: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    public function getPermissionTrackings(string $dni): JsonResponse
    {
        try {
            $user = User::where('dni', $dni)->where('is_active', true)->first();
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Empleado no encontrado'
                ], 404);
            }

            // Obtener trackings activos (pendientes o fuera)
            $trackings = PermissionTracking::where('employee_dni', $dni)
                ->whereIn('tracking_status', [
                    PermissionTracking::STATUS_PENDING,
                    PermissionTracking::STATUS_OUT
                ])
                ->with(['permissionRequest.user', 'permissionRequest.permissionType'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($tracking) {
                    return [
                        'id' => $tracking->id,
                        'permission_request_id' => $tracking->permission_request_id,
                        'status' => $tracking->tracking_status,
                        'departure_datetime' => $tracking->departure_datetime?->toISOString(),
                        'permission_type' => $tracking->permissionRequest?->permissionType?->name,
                        'reason' => $tracking->permissionRequest?->reason,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $trackings,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener trackings: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener trackings'
            ], 500);
        }
    }

    public function getStatus(): JsonResponse
    {
        try {
            $status = [
                'server' => [
                    'name' => config('app.name'),
                    'version' => config('app.version', '1.0.0'),
                    'timestamp' => now()->toISOString(),
                ],
                'database' => [
                    'active_users' => User::where('is_active', true)->count(),
                    'pending_trackings' => PermissionTracking::where('tracking_status', PermissionTracking::STATUS_PENDING)->count(),
                    'out_trackings' => PermissionTracking::where('tracking_status', PermissionTracking::STATUS_OUT)->count(),
                ],
                'agents' => $this->getAgentsStatus(),
            ];

            return response()->json([
                'success' => true,
                'status' => 'operational',
                'data' => $status,
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener estado: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'status' => 'error',
                'message' => 'Error al obtener estado'
            ], 500);
        }
    }

    private function processAccessEvent(User $user, array $eventData): array
    {
        $eventType = $eventData['event_type'];
        $eventDateTime = Carbon::parse($eventData['event_datetime']);

        // Buscar tracking activo para este empleado
        $tracking = PermissionTracking::where('employee_dni', $user->dni)
            ->whereIn('tracking_status', [
                PermissionTracking::STATUS_PENDING,
                PermissionTracking::STATUS_OUT
            ])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$tracking) {
            return [
                'success' => false,
                'message' => 'No se encontró un permiso activo para este empleado'
            ];
        }

        $currentUser = auth()->user();

        if ($eventType === 'exit') {
            // Registrar salida
            if ($tracking->tracking_status === PermissionTracking::STATUS_PENDING) {
                $success = $tracking->registerDeparture($currentUser, 'Registrado por ZKTeco Agent');
                
                if ($success) {
                    // Actualizar la fecha/hora de salida con la del dispositivo
                    $tracking->departure_datetime = $eventDateTime;
                    $tracking->save();

                    return [
                        'success' => true,
                        'message' => 'Salida registrada exitosamente',
                        'tracking_id' => $tracking->id
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'El empleado ya tiene una salida registrada'
            ];
        } elseif ($eventType === 'entry') {
            // Registrar entrada
            if ($tracking->tracking_status === PermissionTracking::STATUS_OUT) {
                $success = $tracking->registerReturn($currentUser, 'Registrado por ZKTeco Agent');

                if ($success) {
                    // Actualizar la fecha/hora de regreso con la del dispositivo
                    $tracking->return_datetime = $eventDateTime;
                    $tracking->calculateActualHours();
                    $tracking->save();

                    // Generar PDF con overlay de tracking
                    try {
                        $pdfService = app(\App\Services\PdfGeneratorService::class);
                        $result = $pdfService->addTrackingOverlay($tracking->permissionRequest);

                        if ($result['success']) {
                            Log::info('PDF con tracking generado por agente', [
                                'permission_id' => $tracking->permissionRequest->id,
                                'pdf_path' => $result['pdf_path'],
                                'agent_id' => $eventData['agent_id'] ?? null
                            ]);
                        } else {
                            Log::warning('No se pudo generar PDF con tracking por agente', [
                                'permission_id' => $tracking->permissionRequest->id,
                                'error' => $result['message']
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error al generar PDF con tracking por agente: ' . $e->getMessage());
                    }

                    return [
                        'success' => true,
                        'message' => 'Entrada registrada exitosamente',
                        'tracking_id' => $tracking->id
                    ];
                }
            }

            return [
                'success' => false,
                'message' => 'El empleado no tiene una salida registrada'
            ];
        }

        return [
            'success' => false,
            'message' => 'Tipo de evento no válido'
        ];
    }

    private function updateAgentConfig(array $agentData): void
    {
        $configKey = "agent_{$agentData['agent_id']}";
        $configData = [
            'name' => $agentData['agent_name'],
            'zkteco_ip' => $agentData['zkteco_ip'],
            'status' => $agentData['status'],
            'last_seen' => now()->toISOString(),
            'registered_at' => now()->toISOString(),
        ];

        cache()->put($configKey, $configData, now()->addDays(7));
    }

    private function updateAgentHeartbeat(array $data): void
    {
        $configKey = "agent_{$data['agent_id']}";
        $existingConfig = cache()->get($configKey, []);
        
        $configData = array_merge($existingConfig, [
            'status' => $data['status'],
            'last_heartbeat' => $data['timestamp'],
            'stats' => $data['stats'] ?? null,
        ]);

        cache()->put($configKey, $configData, now()->addDays(7));
    }

    private function getAgentsStatus(): array
    {
        $agents = [];
        
        for ($i = 1; $i <= 5; $i++) {
            $configKey = "agent_$i";
            $agentData = cache()->get($configKey);
            
            if ($agentData) {
                $lastHeartbeat = isset($agentData['last_heartbeat']) 
                    ? Carbon::parse($agentData['last_heartbeat'])
                    : null;
                
                $isOnline = $lastHeartbeat && $lastHeartbeat->diffInMinutes(now()) < 5;
                
                $agents[] = [
                    'id' => $i,
                    'name' => $agentData['name'] ?? "Agent $i",
                    'status' => $isOnline ? 'online' : 'offline',
                    'zkteco_ip' => $agentData['zkteco_ip'] ?? null,
                    'last_heartbeat' => $lastHeartbeat?->toISOString(),
                    'stats' => $agentData['stats'] ?? null,
                ];
            }
        }

        return $agents;
    }
}