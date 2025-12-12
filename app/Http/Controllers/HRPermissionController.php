<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\PermissionType;
use App\Models\PermissionTracking;
use App\Models\User;
use App\Models\Approval;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HRPermissionController extends Controller
{
    /**
     * Mostrar formulario para crear permiso directo
     */
    public function create()
    {
        $user = Auth::user();

        if (!$user->hasRole(['jefe_rrhh', 'admin'])) {
            abort(403, 'No tiene permisos para acceder a esta función.');
        }

        // Obtener todos los usuarios para el select
        $users = User::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'dni', 'email']);

        // Obtener tipos de permisos activos
        $permissionTypes = PermissionType::active()->get();

        return view('hr.permissions.create', compact('users', 'permissionTypes'));
    }

    /**
     * Guardar permiso directo con estado aprobado y tracking completo
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->hasRole(['jefe_rrhh', 'admin'])) {
            abort(403, 'No tiene permisos para realizar esta acción.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission_type_id' => 'required|exists:permission_types,id',
            'departure_datetime' => 'required|date',
            'return_datetime' => 'required|date|after:departure_datetime',
            'reason' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $employee = User::findOrFail($validated['user_id']);
            $departureDateTime = Carbon::parse($validated['departure_datetime']);
            $returnDateTime = Carbon::parse($validated['return_datetime']);

            // Usar las mismas fechas de salida y regreso como inicio y fin
            $startDateTime = $departureDateTime;
            $endDateTime = $returnDateTime;

            // Calcular horas solicitadas (basadas en salida/regreso real)
            $requestedHours = $departureDateTime->diffInMinutes($returnDateTime) / 60;

            // Crear la solicitud de permiso directamente APROBADA
            $permission = PermissionRequest::create([
                'user_id' => $employee->id,
                'permission_type_id' => $validated['permission_type_id'],
                'reason' => $validated['reason'],
                'status' => PermissionRequest::STATUS_APPROVED,
                'submitted_at' => now(),
                'current_approval_level' => 2,
                'metadata' => [
                    'created_by_hr' => true,
                    'created_by_user_id' => $user->id,
                    'created_by_user_name' => $user->name,
                    'hr_notes' => $validated['notes'] ?? null,
                    'start_datetime' => $startDateTime->toDateTimeString(),
                    'end_datetime' => $endDateTime->toDateTimeString(),
                    'requested_hours' => round($requestedHours, 2),
                ],
            ]);

            // Crear aprobaciones de nivel 1 y 2 (ambas aprobadas por RRHH)
            Approval::create([
                'permission_request_id' => $permission->id,
                'approver_id' => $user->id,
                'approval_level' => 1,
                'status' => 'approved',
                'comments' => 'Aprobado automáticamente - Permiso creado por RRHH',
                'approved_at' => now(),
                'metadata' => [
                    'approval_method' => 'hr_direct_creation',
                ],
            ]);

            Approval::create([
                'permission_request_id' => $permission->id,
                'approver_id' => $user->id,
                'approval_level' => 2,
                'status' => 'approved',
                'comments' => 'Aprobado automáticamente - Permiso creado por RRHH',
                'approved_at' => now(),
                'metadata' => [
                    'approval_method' => 'hr_direct_creation',
                ],
            ]);

            // Calcular horas reales usadas
            $actualHoursUsed = $departureDateTime->diffInMinutes($returnDateTime) / 60;

            // Crear el tracking directamente COMPLETADO
            $tracking = PermissionTracking::create([
                'permission_request_id' => $permission->id,
                'employee_dni' => $employee->dni,
                'departure_datetime' => $departureDateTime,
                'return_datetime' => $returnDateTime,
                'actual_hours_used' => round($actualHoursUsed, 2),
                'tracking_status' => PermissionTracking::STATUS_RETURNED,
                'registered_by_user_id' => $user->id,
                'notes' => 'Tracking registrado directamente por RRHH: ' . ($validated['notes'] ?? 'Sin notas adicionales'),
            ]);

            DB::commit();

            return redirect()
                ->route('permissions.show', $permission)
                ->with('success', 'Permiso creado exitosamente con estado aprobado y tracking completado.');

        } catch (\Exception $e) {
            DB::rollBack();

            \Log::error('Error al crear permiso directo por RRHH', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()
                ->withInput()
                ->with('error', 'Error al crear el permiso: ' . $e->getMessage());
        }
    }

    /**
     * Obtener información de un usuario para preview (AJAX)
     */
    public function getUserInfo(User $user)
    {
        $hrUser = Auth::user();

        if (!$hrUser->hasRole(['jefe_rrhh', 'admin'])) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'dni' => $user->dni,
                'email' => $user->email,
                'department' => $user->department->name ?? 'N/A',
                'position' => $user->position ?? 'N/A',
            ]
        ]);
    }
}
