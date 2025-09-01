<?php

namespace App\Helpers;

use App\Models\Notification;
use App\Models\User;
use App\Models\PermissionRequest;
use App\Jobs\SendEmailNotification;
use App\Jobs\SendBulkNotifications;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotificationHelper
{
    /**
     * Send notification to a single user
     */
    public static function sendToUser(
        User $user, 
        string $type, 
        string $title, 
        string $message, 
        array $data = [], 
        ?User $sender = null,
        string $priority = Notification::PRIORITY_NORMAL,
        string $category = Notification::CATEGORY_PERMISSION,
        bool $sendEmail = true,
        ?string $emailTemplate = null
    ): Notification {
        
        // Crear notificación en BD
        $notification = Notification::create([
            'user_id' => $user->id,
            'sender_id' => $sender?->id,
            'type' => $type,
            'category' => $category,
            'priority' => $priority,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'channel' => $sendEmail ? 'email' : 'database',
        ]);

        // Enviar email si se solicita
        if ($sendEmail && $emailTemplate) {
            static::sendEmailForNotification($notification, $emailTemplate, $data);
        }

        return $notification;
    }

    /**
     * Send notification to multiple users
     */
    public static function sendToUsers(
        Collection $users, 
        string $type, 
        string $title, 
        string $message, 
        array $data = [], 
        ?User $sender = null,
        string $priority = Notification::PRIORITY_NORMAL,
        string $category = Notification::CATEGORY_PERMISSION,
        bool $sendEmail = true,
        ?string $emailTemplate = null
    ): Collection {
        
        $notifications = collect();

        foreach ($users as $user) {
            $notification = static::sendToUser(
                $user, 
                $type, 
                $title, 
                $message, 
                $data, 
                $sender, 
                $priority, 
                $category, 
                false // No enviar emails individuales
            );
            
            $notifications->push($notification);
        }

        // Enviar emails en lote si se solicita
        if ($sendEmail && $emailTemplate) {
            static::sendBulkEmails($users, $title, $emailTemplate, $data);
        }

        return $notifications;
    }

    /**
     * Send permission submitted notification
     */
    public static function sendPermissionSubmittedNotification(
        PermissionRequest $permission, 
        User $approver, 
        ?User $sender = null
    ): Notification {
        
        $sender = $sender ?? $permission->user;
        
        return static::sendToUser(
            user: $approver,
            type: Notification::TYPE_PERMISSION_SUBMITTED,
            title: 'Nueva Solicitud de Permiso',
            message: "Nueva solicitud #{$permission->request_number} de {$permission->user->name} requiere su aprobación",
            data: [
                'permission_id' => $permission->id,
                'request_number' => $permission->request_number,
                'employee_name' => $permission->user->name,
                'permission_type' => $permission->permissionType->name,
                'reason' => $permission->reason,
                'action_url' => route('approvals.show', $permission->id),
            ],
            sender: $sender,
            priority: Notification::PRIORITY_HIGH,
            sendEmail: true,
            emailTemplate: 'emails.permission-submitted'
        );
    }

    /**
     * Send permission approved notification
     */
    public static function sendPermissionApprovedNotification(
        PermissionRequest $permission, 
        User $recipient, 
        User $approver, 
        bool $isFinal = false
    ): Notification {
        
        return static::sendToUser(
            user: $recipient,
            type: Notification::TYPE_PERMISSION_APPROVED,
            title: $isFinal ? 'Permiso Aprobado Completamente' : 'Aprobación Parcial de Permiso',
            message: $isFinal 
                ? "Su solicitud #{$permission->request_number} ha sido aprobada completamente"
                : "Su solicitud #{$permission->request_number} fue aprobada y ahora será revisada por RRHH",
            data: [
                'permission_id' => $permission->id,
                'request_number' => $permission->request_number,
                'approver_name' => $approver->name,
                'approval_level' => $approver->hasRole('jefe_rrhh') ? 'RRHH' : 'Jefe Inmediato',
                'is_final' => $isFinal,
                'permission_type' => $permission->permissionType->name,
                'start_date' => $permission->start_datetime->format('d/m/Y H:i'),
                'end_date' => $permission->end_datetime->format('d/m/Y H:i'),
                'action_url' => route('permissions.show', $permission->id),
            ],
            sender: $approver,
            priority: $isFinal ? Notification::PRIORITY_HIGH : Notification::PRIORITY_NORMAL,
            sendEmail: true,
            emailTemplate: 'emails.permission-approved-employee'
        );
    }

    /**
     * Send permission rejected notification
     */
    public static function sendPermissionRejectedNotification(
        PermissionRequest $permission, 
        User $recipient, 
        User $approver, 
        string $reason
    ): Notification {
        
        return static::sendToUser(
            user: $recipient,
            type: Notification::TYPE_PERMISSION_REJECTED,
            title: 'Solicitud de Permiso Rechazada',
            message: "Su solicitud #{$permission->request_number} ha sido rechazada por {$approver->name}",
            data: [
                'permission_id' => $permission->id,
                'request_number' => $permission->request_number,
                'approver_name' => $approver->name,
                'approval_level' => $approver->hasRole('jefe_rrhh') ? 'RRHH' : 'Jefe Inmediato',
                'rejection_reason' => $reason,
                'permission_type' => $permission->permissionType->name,
                'start_date' => $permission->start_datetime->format('d/m/Y H:i'),
                'end_date' => $permission->end_datetime->format('d/m/Y H:i'),
                'action_url' => route('permissions.show', $permission->id),
            ],
            sender: $approver,
            priority: Notification::PRIORITY_HIGH,
            sendEmail: true,
            emailTemplate: 'emails.permission-rejected'
        );
    }

    /**
     * Send system notification to all users
     */
    public static function sendSystemNotificationToAll(
        string $title, 
        string $message, 
        array $data = [], 
        string $priority = Notification::PRIORITY_NORMAL,
        bool $sendEmail = false
    ): Collection {
        
        $users = User::where('is_active', true)->get();
        
        return static::sendToUsers(
            users: $users,
            type: Notification::TYPE_SYSTEM_MAINTENANCE,
            title: $title,
            message: $message,
            data: $data,
            priority: $priority,
            category: Notification::CATEGORY_SYSTEM,
            sendEmail: $sendEmail
        );
    }

    /**
     * Send notification to all supervisors
     */
    public static function sendToAllSupervisors(
        string $title, 
        string $message, 
        array $data = [], 
        string $type = 'supervisor_alert',
        string $priority = Notification::PRIORITY_NORMAL,
        bool $sendEmail = true
    ): Collection {
        
        $supervisors = User::whereHas('role', function ($q) {
            $q->whereIn('name', ['jefe_inmediato', 'jefe_rrhh', 'admin']);
        })->where('is_active', true)->get();
        
        return static::sendToUsers(
            users: $supervisors,
            type: $type,
            title: $title,
            message: $message,
            data: $data,
            priority: $priority,
            category: Notification::CATEGORY_ADMIN,
            sendEmail: $sendEmail
        );
    }

    /**
     * Send deadline reminder notifications
     */
    public static function sendDeadlineReminders(): void
    {
        try {
            // Recordatorios para aprobaciones pendientes (más de 24 horas)
            $pendingApprovals = PermissionRequest::whereIn('status', [
                PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS,
                PermissionRequest::STATUS_PENDING_HR
            ])
            ->where('submitted_at', '<', now()->subDay())
            ->with(['user', 'permissionType'])
            ->get();

            foreach ($pendingApprovals as $permission) {
                // Determinar quién debe aprobar
                $approvers = static::getApproversForPermission($permission);
                
                foreach ($approvers as $approver) {
                    static::sendToUser(
                        user: $approver,
                        type: Notification::TYPE_DEADLINE_REMINDER,
                        title: 'Recordatorio: Solicitud Pendiente',
                        message: "La solicitud #{$permission->request_number} lleva más de 24 horas pendiente de aprobación",
                        data: [
                            'permission_id' => $permission->id,
                            'request_number' => $permission->request_number,
                            'employee_name' => $permission->user->name,
                            'days_pending' => $permission->submitted_at->diffInDays(now()),
                            'action_url' => route('approvals.show', $permission->id),
                        ],
                        priority: Notification::PRIORITY_HIGH,
                        category: Notification::CATEGORY_REMINDER,
                        sendEmail: true
                    );
                }
            }

            Log::info('Deadline reminder notifications sent', [
                'count' => $pendingApprovals->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error sending deadline reminders', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Clean up old notifications
     */
    public static function cleanupOldNotifications(int $daysOld = 30): int
    {
        try {
            $deleted = Notification::where('created_at', '<', now()->subDays($daysOld))->delete();
            
            Log::info('Old notifications cleaned up', [
                'deleted_count' => $deleted,
                'days_old' => $daysOld
            ]);
            
            return $deleted;
        } catch (\Exception $e) {
            Log::error('Error cleaning up notifications', [
                'error' => $e->getMessage()
            ]);
            
            return 0;
        }
    }

    /**
     * Get notification statistics for dashboard
     */
    public static function getSystemStats(): array
    {
        return [
            'total_notifications' => Notification::count(),
            'unread_notifications' => Notification::unread()->count(),
            'high_priority_unread' => Notification::unread()->where('priority', Notification::PRIORITY_HIGH)->count(),
            'notifications_today' => Notification::whereDate('created_at', today())->count(),
            'notifications_this_week' => Notification::where('created_at', '>=', now()->startOfWeek())->count(),
            'by_category' => Notification::selectRaw('category, count(*) as count')
                              ->groupBy('category')
                              ->pluck('count', 'category')
                              ->toArray(),
            'by_priority' => Notification::selectRaw('priority, count(*) as count')
                              ->groupBy('priority')
                              ->pluck('count', 'priority')
                              ->toArray(),
        ];
    }

    /**
     * Private helper methods
     */
    private static function sendEmailForNotification(Notification $notification, string $template, array $data): void
    {
        try {
            SendEmailNotification::dispatch(
                $notification->user->email,
                $notification->title,
                $template,
                array_merge($data, [
                    'user_name' => $notification->user->name,
                    'notification_id' => $notification->id,
                ])
            );
            
            $notification->markEmailAsSent();
            
        } catch (\Exception $e) {
            Log::error('Error sending notification email', [
                'notification_id' => $notification->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    private static function sendBulkEmails(Collection $users, string $subject, string $template, array $data): void
    {
        try {
            $userIds = $users->pluck('id')->toArray();
            
            SendBulkNotifications::dispatch(
                $userIds,
                $subject,
                $template,
                $data
            );
            
        } catch (\Exception $e) {
            Log::error('Error sending bulk notification emails', [
                'user_count' => $users->count(),
                'error' => $e->getMessage()
            ]);
        }
    }

    private static function getApproversForPermission(PermissionRequest $permission): Collection
    {
        $approvers = collect();

        if ($permission->status === PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS) {
            $supervisor = User::find($permission->user->immediate_supervisor_id);
            if ($supervisor) {
                $approvers->push($supervisor);
            }
        } elseif ($permission->status === PermissionRequest::STATUS_PENDING_HR) {
            $hrChief = User::whereHas('role', function ($q) {
                $q->where('name', 'jefe_rrhh');
            })->where('is_active', true)->first();
            
            if ($hrChief) {
                $approvers->push($hrChief);
            }
        }

        return $approvers;
    }
}