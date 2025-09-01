<?php

namespace App\Listeners;

use App\Events\PermissionRequestRejected;
use App\Jobs\SendEmailNotification;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendRejectionNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(PermissionRequestRejected $event): void
    {
        try {
            $permission = $event->permissionRequest;
            $approver = $event->approver;
            $comments = $event->comments;

            // Notificar al empleado solicitante
            $this->notifyEmployee($permission, $approver, $comments);

            // Notificar al jefe inmediato si el rechazo fue por RRHH
            if ($approver->hasRole('jefe_rrhh') && $permission->user->immediate_supervisor_id) {
                $this->notifyImmediateSupervisor($permission, $approver, $comments);
            }

            Log::info('Rejection notification sent', [
                'permission_id' => $permission->id,
                'approver_id' => $approver->id,
                'rejection_level' => $approver->hasRole('jefe_rrhh') ? 'RRHH' : 'Jefe Inmediato',
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending rejection notification', [
                'error' => $e->getMessage(),
                'permission_id' => $event->permissionRequest->id,
                'approver_id' => $event->approver->id,
            ]);
        }
    }

    private function notifyEmployee($permission, $approver, $comments): void
    {
        // Crear notificación en BD
        $notification = Notification::createForPermissionRejected(
            $permission, 
            $permission->user, 
            $approver, 
            $comments
        );
        
        $approvalLevel = $approver->hasRole('jefe_rrhh') ? 'RRHH' : 'Jefe Inmediato';

        SendEmailNotification::dispatch(
            $permission->user->email,
            'Su Solicitud de Permiso Ha Sido Rechazada',
            'emails.permission-rejected',
            [
                'employee_name' => $permission->user->name,
                'approver_name' => $approver->name,
                'approval_level' => $approvalLevel,
                'permission_type' => $permission->permissionType->name,
                'request_number' => $permission->request_number,
                'start_date' => $permission->start_datetime->format('d/m/Y H:i'),
                'end_date' => $permission->end_datetime->format('d/m/Y H:i'),
                'requested_hours' => $permission->requested_hours,
                'rejection_reason' => $comments,
                'dashboard_url' => route('permissions.index'),
                'new_request_url' => route('permissions.create'),
            ]
        );
        
        // Marcar notificación como enviada por email
        $notification->markEmailAsSent();
    }

    private function notifyImmediateSupervisor($permission, $hrApprover, $comments): void
    {
        $supervisor = User::find($permission->user->immediate_supervisor_id);
        
        if ($supervisor) {
            SendEmailNotification::dispatch(
                $supervisor->email,
                'Permiso de Subordinado Rechazado por RRHH',
                'emails.permission-rejected-supervisor',
                [
                    'supervisor_name' => $supervisor->name,
                    'employee_name' => $permission->user->name,
                    'hr_approver' => $hrApprover->name,
                    'permission_type' => $permission->permissionType->name,
                    'request_number' => $permission->request_number,
                    'start_date' => $permission->start_datetime->format('d/m/Y H:i'),
                    'end_date' => $permission->end_datetime->format('d/m/Y H:i'),
                    'requested_hours' => $permission->requested_hours,
                    'rejection_reason' => $comments,
                    'dashboard_url' => route('approvals.index'),
                ]
            );
        }
    }
}