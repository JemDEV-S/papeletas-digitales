<?php

namespace App\Listeners;

use App\Events\PermissionRequestSubmitted;
use App\Events\PermissionRequestApproved;
use App\Jobs\SendEmailNotification;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendApprovalNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle($event): void
    {
        try {
            if ($event instanceof PermissionRequestSubmitted) {
                $this->handleSubmittedNotification($event);
            } elseif ($event instanceof PermissionRequestApproved) {
                $this->handleApprovedNotification($event);
            }
        } catch (\Exception $e) {
            Log::error('Error sending approval notification', [
                'error' => $e->getMessage(),
                'event_type' => get_class($event),
                'permission_id' => $event->permissionRequest->id ?? null,
            ]);
        }
    }

    private function handleSubmittedNotification(PermissionRequestSubmitted $event): void
    {
        $permission = $event->permissionRequest;
        $approver = $event->approver;

        // Crear notificación en BD
        $notification = Notification::createForPermissionSubmitted($permission, $approver, $permission->user);
        
        // Email al aprobador
        SendEmailNotification::dispatch(
            $approver->email,
            'Nueva Solicitud de Permiso Pendiente de Aprobación',
            'emails.permission-submitted',
            [
                'approver_name' => $approver->name,
                'employee_name' => $permission->user->name,
                'permission_type' => $permission->permissionType->name,
                'request_number' => $permission->request_number,
                'reason' => $permission->reason,
                'approval_url' => route('approvals.show', $permission->id),
                'dashboard_url' => route('approvals.index'),
            ]
        );
        
        // Marcar notificación como enviada por email
        $notification->markEmailAsSent();

        Log::info('Approval notification sent for new submission', [
            'permission_id' => $permission->id,
            'approver_id' => $approver->id,
            'approval_level' => $permission->current_approval_level,
        ]);
    }

    private function handleApprovedNotification(PermissionRequestApproved $event): void
    {
        $permission = $event->permissionRequest;
        $approver = $event->approver;
        $nextApprover = $event->nextApprover;

        // Notificar al empleado solicitante
        $this->notifyEmployee($permission, $approver, $nextApprover !== null);

        // Si hay siguiente aprobador, verificar si es nivel RRHH
        if ($nextApprover) {
            // Si el siguiente aprobador es RRHH, notificar a TODOS los jefes de RRHH
            if ($nextApprover->hasRole('jefe_rrhh')) {
                $this->notifyAllHRChiefs($permission, $approver);
            } else {
                // Si no es RRHH, solo notificar al siguiente aprobador específico
                $this->notifyNextApprover($permission, $nextApprover, $approver);
            }
        }

        // Si es aprobación final, notificar a jefe inmediato también
        if (!$nextApprover && $permission->user->immediate_supervisor_id) {
            $this->notifyImmediateSupervisor($permission, $approver);
        }

        Log::info('Approval notification sent for approved permission', [
            'permission_id' => $permission->id,
            'approver_id' => $approver->id,
            'is_final' => !$nextApprover,
            'next_approver_id' => $nextApprover?->id,
            'notified_all_hr' => $nextApprover && $nextApprover->hasRole('jefe_rrhh'),
        ]);
    }

    private function notifyEmployee($permission, $approver, $needsNextApproval): void
    {
        // Crear notificación en BD
        $notification = Notification::createForPermissionApproved(
            $permission, 
            $permission->user, 
            $approver, 
            !$needsNextApproval
        );
        
        $subject = $needsNextApproval 
            ? 'Su Solicitud de Permiso Ha Sido Aprobada - Pendiente RRHH'
            : 'Su Solicitud de Permiso Ha Sido Aprobada Completamente';

        SendEmailNotification::dispatch(
            $permission->user->email,
            $subject,
            'emails.permission-approved-employee',
            [
                'employee_name' => $permission->user->name,
                'approver_name' => $approver->name,
                'approval_level' => $approver->hasRole('jefe_rrhh') ? 'RRHH' : 'Jefe Inmediato',
                'permission_type' => $permission->permissionType->name,
                'request_number' => $permission->request_number,
                'is_final_approval' => !$needsNextApproval,
                'next_step' => $needsNextApproval ? 'Su solicitud será revisada por RRHH' : null,
                'dashboard_url' => route('permissions.index'),
            ]
        );
        
        // Marcar notificación como enviada por email
        $notification->markEmailAsSent();
    }

    private function notifyNextApprover($permission, $nextApprover, $previousApprover): void
    {
        // Crear notificación para siguiente aprobador
        $notification = Notification::createForPermissionSubmitted($permission, $nextApprover, $previousApprover);
        
        SendEmailNotification::dispatch(
            $nextApprover->email,
            'Nueva Solicitud de Permiso Pendiente - Aprobación RRHH',
            'emails.permission-next-approval',
            [
                'approver_name' => $nextApprover->name,
                'employee_name' => $permission->user->name,
                'previous_approver' => $previousApprover->name,
                'permission_type' => $permission->permissionType->name,
                'request_number' => $permission->request_number,
                'reason' => $permission->reason,
                'approval_url' => route('approvals.show', $permission->id),
                'dashboard_url' => route('approvals.index'),
            ]
        );
        
        // Marcar notificación como enviada por email
        $notification->markEmailAsSent();
    }

    private function notifyImmediateSupervisor($permission, $hrApprover): void
    {
        $supervisor = User::find($permission->user->immediate_supervisor_id);

        if ($supervisor) {
            SendEmailNotification::dispatch(
                $supervisor->email,
                'Permiso Aprobado para su Subordinado',
                'emails.permission-approved-supervisor',
                [
                    'supervisor_name' => $supervisor->name,
                    'employee_name' => $permission->user->name,
                    'hr_approver' => $hrApprover->name,
                    'permission_type' => $permission->permissionType->name,
                    'request_number' => $permission->request_number,
                    'submitted_date' => $permission->submitted_at?->format('d/m/Y H:i') ?? 'N/A',
                    'permission_reason' => $permission->reason,
                    'dashboard_url' => route('approvals.index'),
                ]
            );
        }
    }

    /**
     * Notificar a TODOS los jefes de RRHH cuando una solicitud pasa al nivel 2
     */
    private function notifyAllHRChiefs($permission, $previousApprover): void
    {
        // Obtener TODOS los usuarios con rol jefe_rrhh
        $allHRChiefs = User::whereHas('role', function ($query) {
            $query->where('name', 'jefe_rrhh');
        })->get();

        if ($allHRChiefs->isEmpty()) {
            Log::warning('No HR chiefs found to notify', [
                'permission_id' => $permission->id,
            ]);
            return;
        }

        Log::info('Notifying all HR chiefs', [
            'permission_id' => $permission->id,
            'hr_chiefs_count' => $allHRChiefs->count(),
            'hr_chiefs_ids' => $allHRChiefs->pluck('id')->toArray(),
        ]);

        // Notificar a cada jefe de RRHH
        foreach ($allHRChiefs as $hrChief) {
            // Crear notificación en BD para cada jefe de RRHH
            $notification = Notification::createForPermissionSubmitted(
                $permission,
                $hrChief,
                $previousApprover
            );

            // Enviar email a cada jefe de RRHH
            SendEmailNotification::dispatch(
                $hrChief->email,
                'Nueva Solicitud de Permiso Pendiente - Aprobación RRHH',
                'emails.permission-next-approval',
                [
                    'approver_name' => $hrChief->name,
                    'employee_name' => $permission->user->name,
                    'previous_approver' => $previousApprover->name,
                    'permission_type' => $permission->permissionType->name,
                    'request_number' => $permission->request_number,
                    'reason' => $permission->reason,
                    'approval_url' => route('approvals.show', $permission->id),
                    'dashboard_url' => route('approvals.index'),
                ]
            );

            // Marcar notificación como enviada por email
            $notification->markEmailAsSent();
        }
    }
}