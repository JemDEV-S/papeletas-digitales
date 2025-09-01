<?php

namespace Database\Seeders;

use App\Models\Notification;
use App\Models\User;
use App\Models\PermissionRequest;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Solo crear notificaciones de ejemplo si no existen
        if (Notification::count() > 0) {
            $this->command->info('Notifications already exist. Skipping seeder.');
            return;
        }

        $this->command->info('Creating sample notifications...');

        // Obtener usuarios para las notificaciones de ejemplo
        $admin = User::whereHas('role', function ($q) {
            $q->where('name', 'admin');
        })->first();

        $hrChief = User::whereHas('role', function ($q) {
            $q->where('name', 'jefe_rrhh');
        })->first();

        $supervisor = User::whereHas('role', function ($q) {
            $q->where('name', 'jefe_inmediato');
        })->first();

        $employee = User::whereHas('role', function ($q) {
            $q->where('name', 'empleado');
        })->first();

        // Obtener una solicitud de permiso de ejemplo
        $permissionRequest = PermissionRequest::first();

        $notifications = [];

        // Notificaciones para administrador
        if ($admin) {
            $notifications[] = [
                'uuid' => \Str::uuid(),
                'user_id' => $admin->id,
                'sender_id' => null,
                'type' => Notification::TYPE_SYSTEM_MAINTENANCE,
                'category' => Notification::CATEGORY_SYSTEM,
                'priority' => Notification::PRIORITY_HIGH,
                'title' => 'Mantenimiento Programado del Sistema',
                'message' => 'Se realizará mantenimiento el próximo domingo de 2:00 AM a 6:00 AM',
                'data' => json_encode([
                    'maintenance_date' => now()->addWeek()->format('Y-m-d'),
                    'duration' => '4 horas',
                    'affected_services' => ['Web Interface', 'Email Notifications'],
                ]),
                'is_broadcast' => true,
                'channel' => 'email',
                'expires_at' => now()->addWeek(),
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ];

            $notifications[] = [
                'uuid' => \Str::uuid(),
                'user_id' => $admin->id,
                'sender_id' => null,
                'type' => 'system_stats',
                'category' => Notification::CATEGORY_ADMIN,
                'priority' => Notification::PRIORITY_NORMAL,
                'title' => 'Reporte Semanal de Actividad',
                'message' => 'Resumen de actividad del sistema de la última semana',
                'data' => json_encode([
                    'total_requests' => 45,
                    'approved_requests' => 38,
                    'rejected_requests' => 7,
                    'active_users' => 23,
                ]),
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ];
        }

        // Notificaciones para jefe de RRHH
        if ($hrChief) {
            $notifications[] = [
                'uuid' => \Str::uuid(),
                'user_id' => $hrChief->id,
                'sender_id' => $supervisor?->id,
                'type' => Notification::TYPE_PERMISSION_SUBMITTED,
                'category' => Notification::CATEGORY_PERMISSION,
                'priority' => Notification::PRIORITY_HIGH,
                'title' => 'Nueva Solicitud de Permiso Pendiente',
                'message' => 'Solicitud de permiso médico requiere aprobación final de RRHH',
                'permission_request_id' => $permissionRequest?->id,
                'data' => json_encode([
                    'permission_id' => $permissionRequest?->id,
                    'request_number' => $permissionRequest?->request_number ?? 'PAP-2025-000001',
                    'employee_name' => $employee?->name ?? 'Juan Pérez',
                    'permission_type' => 'Permiso Médico',
                    'start_date' => now()->addDays(3)->format('d/m/Y H:i'),
                    'end_date' => now()->addDays(3)->addHours(4)->format('d/m/Y H:i'),
                    'requested_hours' => 4,
                    'action_url' => route('approvals.show', $permissionRequest?->id ?? 1),
                ]),
                'reference_type' => PermissionRequest::class,
                'reference_id' => $permissionRequest?->id,
                'created_at' => now()->subMinutes(30),
                'updated_at' => now()->subMinutes(30),
            ];

            $notifications[] = [
                'uuid' => \Str::uuid(),
                'user_id' => $hrChief->id,
                'sender_id' => null,
                'type' => Notification::TYPE_DEADLINE_REMINDER,
                'category' => Notification::CATEGORY_REMINDER,
                'priority' => Notification::PRIORITY_NORMAL,
                'title' => 'Recordatorio: Evaluaciones Pendientes',
                'message' => 'Tiene 3 evaluaciones de desempeño pendientes de completar',
                'data' => json_encode([
                    'pending_evaluations' => 3,
                    'deadline' => now()->addDays(7)->format('d/m/Y'),
                    'employees' => ['Ana García', 'Carlos López', 'María Rodríguez'],
                ]),
                'created_at' => now()->subHours(1),
                'updated_at' => now()->subHours(1),
            ];
        }

        // Notificaciones para supervisor
        if ($supervisor) {
            $notifications[] = [
                'uuid' => \Str::uuid(),
                'user_id' => $supervisor->id,
                'sender_id' => $employee?->id,
                'type' => Notification::TYPE_PERMISSION_SUBMITTED,
                'category' => Notification::CATEGORY_PERMISSION,
                'priority' => Notification::PRIORITY_HIGH,
                'title' => 'Nueva Solicitud de Permiso',
                'message' => 'Su subordinado ha enviado una nueva solicitud de permiso personal',
                'permission_request_id' => $permissionRequest?->id,
                'data' => json_encode([
                    'permission_id' => $permissionRequest?->id,
                    'request_number' => $permissionRequest?->request_number ?? 'PAP-2025-000002',
                    'employee_name' => $employee?->name ?? 'Juan Pérez',
                    'permission_type' => 'Permiso Personal',
                    'start_date' => now()->addDays(5)->format('d/m/Y H:i'),
                    'end_date' => now()->addDays(5)->addHours(2)->format('d/m/Y H:i'),
                    'requested_hours' => 2,
                    'reason' => 'Trámites personales',
                    'action_url' => route('approvals.show', $permissionRequest?->id ?? 1),
                ]),
                'reference_type' => PermissionRequest::class,
                'reference_id' => $permissionRequest?->id,
                'created_at' => now()->subMinutes(15),
                'updated_at' => now()->subMinutes(15),
            ];
        }

        // Notificaciones para empleado
        if ($employee) {
            $notifications[] = [
                'uuid' => \Str::uuid(),
                'user_id' => $employee->id,
                'sender_id' => $supervisor?->id,
                'type' => Notification::TYPE_PERMISSION_APPROVED,
                'category' => Notification::CATEGORY_PERMISSION,
                'priority' => Notification::PRIORITY_HIGH,
                'title' => 'Solicitud de Permiso Aprobada',
                'message' => 'Su solicitud de permiso ha sido aprobada por su jefe inmediato',
                'permission_request_id' => $permissionRequest?->id,
                'data' => json_encode([
                    'permission_id' => $permissionRequest?->id,
                    'request_number' => $permissionRequest?->request_number ?? 'PAP-2025-000003',
                    'approver_name' => $supervisor?->name ?? 'Supervisor',
                    'approval_level' => 'Jefe Inmediato',
                    'is_final' => false,
                    'permission_type' => 'Permiso de Lactancia',
                    'start_date' => now()->addDays(1)->format('d/m/Y H:i'),
                    'end_date' => now()->addDays(1)->addHour()->format('d/m/Y H:i'),
                    'action_url' => route('permissions.show', $permissionRequest?->id ?? 1),
                ]),
                'reference_type' => PermissionRequest::class,
                'reference_id' => $permissionRequest?->id,
                'read_at' => now()->subMinutes(5), // Marcada como leída
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subMinutes(5),
            ];

            $notifications[] = [
                'uuid' => \Str::uuid(),
                'user_id' => $employee->id,
                'sender_id' => $hrChief?->id,
                'type' => Notification::TYPE_PERMISSION_REJECTED,
                'category' => Notification::CATEGORY_PERMISSION,
                'priority' => Notification::PRIORITY_HIGH,
                'title' => 'Solicitud de Permiso Rechazada',
                'message' => 'Su solicitud de vacaciones ha sido rechazada por RRHH',
                'permission_request_id' => $permissionRequest?->id,
                'data' => json_encode([
                    'permission_id' => $permissionRequest?->id,
                    'request_number' => 'PAP-2025-000004',
                    'approver_name' => $hrChief?->name ?? 'Jefe RRHH',
                    'approval_level' => 'RRHH',
                    'rejection_reason' => 'Período no disponible debido a cierre de año fiscal',
                    'permission_type' => 'Vacaciones',
                    'start_date' => now()->addDays(10)->format('d/m/Y'),
                    'end_date' => now()->addDays(15)->format('d/m/Y'),
                    'action_url' => route('permissions.show', $permissionRequest?->id ?? 1),
                ]),
                'reference_type' => PermissionRequest::class,
                'reference_id' => $permissionRequest?->id,
                'created_at' => now()->subDays(1),
                'updated_at' => now()->subDays(1),
            ];
        }

        // Insertar notificaciones en la base de datos
        foreach ($notifications as $notification) {
            Notification::create($notification);
        }

        $this->command->info('Created ' . count($notifications) . ' sample notifications.');
        
        // Mostrar estadísticas
        $stats = [
            'Total notifications' => Notification::count(),
            'Unread notifications' => Notification::unread()->count(),
            'High priority' => Notification::where('priority', Notification::PRIORITY_HIGH)->count(),
            'By category' => Notification::selectRaw('category, count(*) as count')
                               ->groupBy('category')
                               ->pluck('count', 'category')
                               ->toArray(),
        ];

        $this->command->table(['Metric', 'Value'], collect($stats)->map(function ($value, $key) {
            return [$key, is_array($value) ? json_encode($value) : $value];
        })->toArray());
    }
}