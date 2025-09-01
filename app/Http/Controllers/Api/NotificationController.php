<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class NotificationController extends Controller
{
    /**
     * Check for new notifications
     */
    public function checkForUpdates(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        \Log::info('Checking for notification updates', ['user_id' => $user->id]);
        
        // Obtener timestamp de la última verificación  
        $lastCheck = $request->get('last_check', now()->subMinutes(5)->timestamp);
        $lastCheckTime = \Carbon\Carbon::createFromTimestamp($lastCheck);
        
        \Log::info('Last check time', ['last_check' => $lastCheckTime]);
        
        // Obtener todas las notificaciones recientes para debug
        $allNotifications = $user->notifications()->latest()->take(5)->get();
        \Log::info('Recent notifications', ['count' => $allNotifications->count()]);
        
        // Obtener notificaciones nuevas desde la última verificación
        $notifications = $user->notifications()
            ->where('created_at', '>', $lastCheckTime)
            ->latest()
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'uuid' => $notification->uuid ?? null,
                    'type' => $notification->type,
                    'category' => $notification->category ?? 'permission',
                    'priority' => $notification->priority ?? 'normal',
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data ?? [],
                    'is_read' => !is_null($notification->read_at),
                    'created_at' => $notification->created_at->toISOString(),
                    'action' => $notification->data['action_url'] ?? null,
                ];
            });
            
        $unreadCount = $user->notifications()->whereNull('read_at')->count();
        
        \Log::info('Check results', [
            'new_notifications' => $notifications->count(),
            'unread_count' => $unreadCount
        ]);
        
        return response()->json([
            'new_notifications' => $notifications->count() > 0,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'timestamp' => now()->timestamp,
            'debug' => [
                'last_check' => $lastCheckTime,
                'total_notifications' => $allNotifications->count(),
                'user_id' => $user->id
            ]
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = Auth::user();
        
        // Debug info
        \Log::info('Getting unread count for user', ['user_id' => $user->id]);
        
        $total = $user->notifications()->count();
        $unread = $user->notifications()->whereNull('read_at')->count();
        
        \Log::info('Notification counts', ['total' => $total, 'unread' => $unread]);
        
        return response()->json([
            'count' => $unread,
            'total' => $total,
            'user_id' => $user->id,
            'debug' => true
        ]);
    }

    /**
     * Get approval statistics for real-time updates
     */
    public function getApprovalStats(): JsonResponse
    {
        $user = Auth::user();
        
        // Cache stats for 1 minute to avoid excessive queries
        $cacheKey = "approval_stats_user_{$user->id}";
        $stats = Cache::remember($cacheKey, 60, function () use ($user) {
            return $this->calculateApprovalStats($user);
        });
        
        return response()->json($stats);
    }

    /**
     * Mark notifications as read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();
        $notificationIds = $request->input('notification_ids', []);
        
        if (empty($notificationIds)) {
            // Marcar todas como leídas si no se especifican IDs
            $marked = $user->markAllNotificationsAsRead();
        } else {
            // Marcar específicas como leídas
            $marked = Notification::whereIn('id', $notificationIds)
                ->where('user_id', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }
        
        return response()->json([
            'success' => true,
            'marked_count' => $marked,
            'new_unread_count' => $user->fresh()->unread_notifications_count
        ]);
    }

    /**
     * Get permissions list for real-time updates
     */
    public function getPermissionsList(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $permissions = $user->permissionRequests()
            ->with(['permissionType', 'approvals.approver'])
            ->latest('submitted_at')
            ->limit(10)
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'request_number' => $permission->request_number,
                    'status' => $permission->status,
                    'status_label' => $permission->getStatusLabel(),
                    'status_color' => $permission->getStatusColor(),
                    'permission_type' => $permission->permissionType->name,
                    'start_datetime' => $permission->start_datetime->toISOString(),
                    'end_datetime' => $permission->end_datetime->toISOString(),
                    'requested_hours' => $permission->requested_hours,
                    'submitted_at' => $permission->submitted_at?->toISOString(),
                    'current_approval_level' => $permission->current_approval_level,
                ];
            });
        
        return response()->json($permissions);
    }

    /**
     * Get approvals list for real-time updates
     */
    public function getApprovalsList(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Obtener solicitudes pendientes según el rol del usuario
        $query = \App\Models\PermissionRequest::with(['user.department', 'permissionType']);
        
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
        } else {
            // Usuario normal no debería ver nada
            $query->whereRaw('1 = 0');
        }
        
        $approvals = $query->latest('submitted_at')
            ->limit(10)
            ->get()
            ->map(function ($permission) {
                return [
                    'id' => $permission->id,
                    'request_number' => $permission->request_number,
                    'status' => $permission->status,
                    'status_label' => $permission->getStatusLabel(),
                    'status_color' => $permission->getStatusColor(),
                    'user' => [
                        'name' => $permission->user->name,
                        'department' => $permission->user->department->name ?? null,
                    ],
                    'permission_type' => $permission->permissionType->name,
                    'start_datetime' => $permission->start_datetime->toISOString(),
                    'end_datetime' => $permission->end_datetime->toISOString(),
                    'requested_hours' => $permission->requested_hours,
                    'submitted_at' => $permission->submitted_at?->toISOString(),
                    'approval_url' => route('approvals.show', $permission->id),
                ];
            });
        
        return response()->json($approvals);
    }

    /**
     * Get all notifications for user with pagination
     */
    public function getAllNotifications(Request $request): JsonResponse
    {
        $user = Auth::user();
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 15);
        $filter = $request->get('filter', 'all'); // all, unread, read
        $category = $request->get('category'); // permission, system, admin, reminder
        
        $query = $user->notifications()
            ->latest();
            
        // Aplicar filtros
        if ($filter === 'unread') {
            $query->unread();
        } elseif ($filter === 'read') {
            $query->read();
        }
        
        if ($category) {
            $query->ofCategory($category);
        }
        
        $notifications = $query->paginate($perPage, ['*'], 'page', $page);
        
        return response()->json([
            'notifications' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'has_more' => $notifications->hasMorePages(),
            ],
            'filters' => [
                'current_filter' => $filter,
                'current_category' => $category,
            ]
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(): JsonResponse
    {
        $user = Auth::user();
        
        $stats = [
            'total' => $user->notifications()->count(),
            'unread' => $user->unread_notifications_count,
            'high_priority' => $user->notifications()->highPriority()->unread()->count(),
            'by_category' => $user->notifications()
                ->selectRaw('category, count(*) as count')
                ->where('read_at', null)
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'recent_count' => $user->notifications()->recent()->count(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Calculate approval statistics
     */
    private function calculateApprovalStats($user): array
    {
        $baseQuery = \App\Models\PermissionRequest::query();

        // Filtrar según el rol del usuario
        if ($user->hasRole('jefe_inmediato') && !$user->hasRole('jefe_rrhh')) {
            $subordinateIds = $user->subordinates->pluck('id');
            $baseQuery->whereIn('user_id', $subordinateIds);
        } elseif (!$user->hasRole('jefe_rrhh') && !$user->hasRole('admin')) {
            $baseQuery->whereRaw('1 = 0'); // No mostrar nada si no tiene permisos
        }

        return [
            'pending_immediate' => (clone $baseQuery)
                ->where('status', 'pending_immediate_boss')
                ->count(),
            'pending_hr' => $user->hasRole('jefe_rrhh') || $user->hasRole('admin') 
                ? \App\Models\PermissionRequest::where('status', 'pending_hr')->count()
                : 0,
            'approved_today' => $user->approvals()
                ->where('status', 'approved')
                ->whereDate('approved_at', today())
                ->count(),
            'rejected_today' => $user->approvals()
                ->where('status', 'rejected')
                ->whereDate('approved_at', today())
                ->count(),
            'total_this_month' => $user->approvals()
                ->whereMonth('approved_at', now()->month)
                ->whereYear('approved_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Delete notification
     */
    public function deleteNotification(Request $request, $id): JsonResponse
    {
        $user = Auth::user();
        
        $notification = $user->notifications()->find($id);
        
        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }
        
        $notification->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully',
            'new_unread_count' => $user->fresh()->unread_notifications_count
        ]);
    }
    
    /**
     * Clear all notifications for user
     */
    public function clearAllNotifications(): JsonResponse
    {
        $user = Auth::user();
        
        $deleted = $user->notifications()->delete();
        
        return response()->json([
            'success' => true,
            'deleted_count' => $deleted,
            'message' => "Deleted {$deleted} notifications"
        ]);
    }

    /**
     * Get pending approvals count (simplified endpoint)
     */
    public function pendingApprovalsCount(): JsonResponse
    {
        $user = auth()->user();
        $count = 0;
        
        if ($user->canApprove(new \App\Models\PermissionRequest())) {
            $query = \App\Models\PermissionRequest::query();
            
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
            } else {
                $query->whereRaw('1 = 0');
            }
            
            $count = $query->count();
        }

        return response()->json(['count' => $count]);
    }
}