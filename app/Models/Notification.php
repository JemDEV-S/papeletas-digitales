<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'sender_id',
        'type',
        'category',
        'priority',
        'title',
        'message',
        'data',
        'permission_request_id',
        'read_at',
        'sent_at',
        'delivered_at',
        'is_broadcast',
        'is_email_sent',
        'expires_at',
        'channel',
        'reference_type',
        'reference_id',
        'metadata',
    ];

    protected $casts = [
        'data' => 'array',
        'metadata' => 'array',
        'read_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_broadcast' => 'boolean',
        'is_email_sent' => 'boolean',
    ];

    /**
     * Notification types constants
     */
    const TYPE_PERMISSION_SUBMITTED = 'permission_submitted';
    const TYPE_PERMISSION_APPROVED = 'permission_approved';
    const TYPE_PERMISSION_REJECTED = 'permission_rejected';
    const TYPE_PERMISSION_CANCELLED = 'permission_cancelled';
    const TYPE_PERMISSION_EXPIRED = 'permission_expired';
    const TYPE_SYSTEM_MAINTENANCE = 'system_maintenance';
    const TYPE_BULK_APPROVAL = 'bulk_approval';
    const TYPE_DEADLINE_REMINDER = 'deadline_reminder';

    /**
     * Categories constants
     */
    const CATEGORY_PERMISSION = 'permission';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_ADMIN = 'admin';
    const CATEGORY_REMINDER = 'reminder';

    /**
     * Priority levels constants
     */
    const PRIORITY_LOW = 'low';
    const PRIORITY_NORMAL = 'normal';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($notification) {
            if (empty($notification->uuid)) {
                $notification->uuid = Str::uuid();
            }
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function permissionRequest()
    {
        return $this->belongsTo(PermissionRequest::class);
    }

    /**
     * Polymorphic relationship for reference
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Scopes
     */
    public function scopeUnread(Builder $query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead(Builder $query)
    {
        return $query->whereNotNull('read_at');
    }

    public function scopeOfType(Builder $query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeOfCategory(Builder $query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeForUser(Builder $query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByPriority(Builder $query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeNotExpired(Builder $query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeExpired(Builder $query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeRecent(Builder $query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeHighPriority(Builder $query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    /**
     * Static methods for creating notifications
     */
    public static function createForPermissionSubmitted(PermissionRequest $permission, User $approver, ?User $sender = null): self
    {
        // Check if notification already exists for this permission and approver
        $existingNotification = self::where('user_id', $approver->id)
            ->where('permission_request_id', $permission->id)
            ->where('type', self::TYPE_PERMISSION_SUBMITTED)
            ->whereDate('created_at', today()) // Only check today's notifications
            ->first();
            
        if ($existingNotification) {
            \Log::info('Duplicate notification prevented', [
                'permission_id' => $permission->id,
                'approver_id' => $approver->id,
                'existing_notification_id' => $existingNotification->id
            ]);
            return $existingNotification;
        }

        return self::create([
            'user_id' => $approver->id,
            'sender_id' => $sender?->id ?? $permission->user_id,
            'type' => self::TYPE_PERMISSION_SUBMITTED,
            'category' => self::CATEGORY_PERMISSION,
            'priority' => self::PRIORITY_HIGH,
            'title' => 'Nueva Solicitud de Permiso',
            'message' => "Nueva solicitud #{$permission->request_number} de {$permission->user->name} requiere su aprobación",
            'permission_request_id' => $permission->id,
            'data' => [
                'permission_id' => $permission->id,
                'request_number' => $permission->request_number,
                'employee_name' => $permission->user->name,
                'permission_type' => $permission->permissionType->name,
                'action_url' => route('approvals.show', $permission->id),
            ],
            'reference_type' => PermissionRequest::class,
            'reference_id' => $permission->id,
        ]);
    }

    public static function createForPermissionApproved(PermissionRequest $permission, User $recipient, User $approver, bool $isFinal = false): self
    {
        // Check if notification already exists for this permission, recipient and approval action
        $existingNotification = self::where('user_id', $recipient->id)
            ->where('permission_request_id', $permission->id)
            ->where('type', self::TYPE_PERMISSION_APPROVED)
            ->where('sender_id', $approver->id)
            ->whereDate('created_at', today()) // Only check today's notifications
            ->first();
            
        if ($existingNotification) {
            \Log::info('Duplicate approved notification prevented', [
                'permission_id' => $permission->id,
                'recipient_id' => $recipient->id,
                'approver_id' => $approver->id,
                'existing_notification_id' => $existingNotification->id
            ]);
            return $existingNotification;
        }

        return self::create([
            'user_id' => $recipient->id,
            'sender_id' => $approver->id,
            'type' => self::TYPE_PERMISSION_APPROVED,
            'category' => self::CATEGORY_PERMISSION,
            'priority' => $isFinal ? self::PRIORITY_HIGH : self::PRIORITY_NORMAL,
            'title' => $isFinal ? 'Permiso Aprobado Completamente' : 'Aprobación Parcial de Permiso',
            'message' => $isFinal 
                ? "Su solicitud #{$permission->request_number} ha sido aprobada completamente"
                : "Su solicitud #{$permission->request_number} fue aprobada y ahora será revisada por RRHH",
            'permission_request_id' => $permission->id,
            'data' => [
                'permission_id' => $permission->id,
                'request_number' => $permission->request_number,
                'approver_name' => $approver->name,
                'approval_level' => $approver->hasRole('jefe_rrhh') ? 'RRHH' : 'Jefe Inmediato',
                'is_final' => $isFinal,
                'permission_type' => $permission->permissionType->name,
                'action_url' => route('permissions.show', $permission->id),
            ],
            'reference_type' => PermissionRequest::class,
            'reference_id' => $permission->id,
        ]);
    }

    public static function createForPermissionRejected(PermissionRequest $permission, User $recipient, User $approver, string $reason): self
    {
        // Check if notification already exists for this permission, recipient and rejection action
        $existingNotification = self::where('user_id', $recipient->id)
            ->where('permission_request_id', $permission->id)
            ->where('type', self::TYPE_PERMISSION_REJECTED)
            ->where('sender_id', $approver->id)
            ->whereDate('created_at', today()) // Only check today's notifications
            ->first();
            
        if ($existingNotification) {
            \Log::info('Duplicate rejected notification prevented', [
                'permission_id' => $permission->id,
                'recipient_id' => $recipient->id,
                'approver_id' => $approver->id,
                'existing_notification_id' => $existingNotification->id
            ]);
            return $existingNotification;
        }

        return self::create([
            'user_id' => $recipient->id,
            'sender_id' => $approver->id,
            'type' => self::TYPE_PERMISSION_REJECTED,
            'category' => self::CATEGORY_PERMISSION,
            'priority' => self::PRIORITY_HIGH,
            'title' => 'Solicitud de Permiso Rechazada',
            'message' => "Su solicitud #{$permission->request_number} ha sido rechazada por {$approver->name}",
            'permission_request_id' => $permission->id,
            'data' => [
                'permission_id' => $permission->id,
                'request_number' => $permission->request_number,
                'approver_name' => $approver->name,
                'approval_level' => $approver->hasRole('jefe_rrhh') ? 'RRHH' : 'Jefe Inmediato',
                'rejection_reason' => $reason,
                'permission_type' => $permission->permissionType->name,
                'action_url' => route('permissions.show', $permission->id),
            ],
            'reference_type' => PermissionRequest::class,
            'reference_id' => $permission->id,
        ]);
    }

    public static function createSystemNotification(User $user, string $title, string $message, array $data = []): self
    {
        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_SYSTEM_MAINTENANCE,
            'category' => self::CATEGORY_SYSTEM,
            'priority' => self::PRIORITY_NORMAL,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Instance methods
     */
    public function markAsRead(): bool
    {
        if ($this->isRead()) {
            return true;
        }

        return $this->update(['read_at' => now()]);
    }

    public function markAsUnread(): bool
    {
        return $this->update(['read_at' => null]);
    }

    public function markAsDelivered(): bool
    {
        return $this->update(['delivered_at' => now()]);
    }

    public function markEmailAsSent(): bool
    {
        return $this->update([
            'is_email_sent' => true,
            'sent_at' => now(),
        ]);
    }

    public function markAsBroadcast(): bool
    {
        return $this->update(['is_broadcast' => true]);
    }

    public function isRead(): bool
    {
        return !is_null($this->read_at);
    }

    public function isUnread(): bool
    {
        return is_null($this->read_at);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isHighPriority(): bool
    {
        return in_array($this->priority, [self::PRIORITY_HIGH, self::PRIORITY_URGENT]);
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'gray',
            self::PRIORITY_NORMAL => 'blue',
            self::PRIORITY_HIGH => 'yellow',
            self::PRIORITY_URGENT => 'red',
            default => 'blue',
        };
    }

    public function getPriorityIconAttribute(): string
    {
        return match($this->priority) {
            self::PRIORITY_LOW => 'fas fa-info-circle',
            self::PRIORITY_NORMAL => 'fas fa-bell',
            self::PRIORITY_HIGH => 'fas fa-exclamation-triangle',
            self::PRIORITY_URGENT => 'fas fa-exclamation-circle',
            default => 'fas fa-bell',
        };
    }

    public function getCategoryIconAttribute(): string
    {
        return match($this->category) {
            self::CATEGORY_PERMISSION => 'fas fa-file-alt',
            self::CATEGORY_SYSTEM => 'fas fa-cog',
            self::CATEGORY_ADMIN => 'fas fa-shield-alt',
            self::CATEGORY_REMINDER => 'fas fa-clock',
            default => 'fas fa-bell',
        };
    }

    /**
     * Bulk operations
     */
    public static function markAllAsReadForUser(int $userId): int
    {
        return self::where('user_id', $userId)
                  ->whereNull('read_at')
                  ->update(['read_at' => now()]);
    }

    public static function deleteExpiredNotifications(): int
    {
        return self::expired()->delete();
    }

    public static function deleteOldNotifications(int $days = 30): int
    {
        return self::where('created_at', '<', now()->subDays($days))->delete();
    }

    /**
     * Statistics methods
     */
    public static function getUnreadCountForUser(int $userId): int
    {
        return self::forUser($userId)->unread()->count();
    }

    public static function getRecentNotificationsForUser(int $userId, int $limit = 10)
    {
        return self::forUser($userId)
                  ->with(['sender', 'permissionRequest'])
                  ->latest()
                  ->limit($limit)
                  ->get();
    }

    public static function getNotificationStats(): array
    {
        return [
            'total' => self::count(),
            'unread' => self::unread()->count(),
            'high_priority' => self::highPriority()->count(),
            'expired' => self::expired()->count(),
            'by_type' => self::selectRaw('type, count(*) as count')
                            ->groupBy('type')
                            ->pluck('count', 'type')
                            ->toArray(),
            'by_category' => self::selectRaw('category, count(*) as count')
                               ->groupBy('category')
                               ->pluck('count', 'category')
                               ->toArray(),
        ];
    }
}