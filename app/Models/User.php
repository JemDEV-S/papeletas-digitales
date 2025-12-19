<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dni',
        'first_name',
        'last_name',
        'name',
        'email',
        'password',
        'department_id',
        'role_id',
        'immediate_supervisor_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'two_factor_expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Set the name attribute based on first_name and last_name.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            $user->name = $user->first_name . ' ' . $user->last_name;
        });
    }

    /**
     * Relationships
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function immediateSupervisor()
    {
        return $this->belongsTo(User::class, 'immediate_supervisor_id');
    }

    public function subordinates()
    {
        return $this->hasMany(User::class, 'immediate_supervisor_id');
    }

    public function permissionRequests()
    {
        return $this->hasMany(PermissionRequest::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approver_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function sentNotifications()
    {
        return $this->hasMany(Notification::class, 'sender_id');
    }


    /**
     * Check if user is a supervisor
     */
    public function isSupervisor(): bool
    {
        return $this->subordinates()->exists();
    }

    /**
     * Check if user is HR chief
     */
    public function isHRChief(): bool
    {
        return $this->hasRole('jefe_rrhh');
    }

    /**
     * Check if user can approve a permission request
     */
    public function canApprove(PermissionRequest $request): bool
    {
        // Cargar relación user si no está cargada
        if (!$request->relationLoaded('user')) {
            $request->load('user');
        }

        // Si no hay request o no tiene usuario, solo verificar si puede aprobar en general
        if (!$request || !$request->user) {
            return $this->isSupervisor() || $this->isHRChief();
        }

        // Si es el jefe inmediato del solicitante
        if ($request->user->immediate_supervisor_id === $this->id) {
            return true;
        }

        // Si es jefe de RRHH
        if ($this->isHRChief()) {
            return true;
        }

        return false;
    }

    /**
     * Get pending approvals for this user
     */
    public function pendingApprovals()
    {
        return $this->approvals()->where('status', 'pending');
    }

    /**
     * Get permission requests pending approval by this user
     */
    public function pendingPermissionRequests()
    {
        $query = PermissionRequest::query();

        // Si es jefe inmediato
        if ($this->isSupervisor()) {
            $subordinateIds = $this->subordinates->pluck('id');
            $query->whereIn('user_id', $subordinateIds)
                  ->where('status', 'pending_immediate_boss');
        }

        // Si es jefe de RRHH
        if ($this->isHRChief()) {
            $query->orWhere('status', 'pending_hr');
        }

        return $query;
    }

    /**
     * Get unread notifications for this user
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread();
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(): int
    {
        return Notification::markAllAsReadForUser($this->id);
    }

    /**
     * Get recent notifications
     */
    public function getRecentNotifications(int $limit = 10)
    {
        return Notification::getRecentNotificationsForUser($this->id, $limit);
    }

    /**
     * Create notification for this user
     */
    public function notify(string $type, string $title, string $message, array $data = [], ?User $sender = null): Notification
    {
        return Notification::create([
            'user_id' => $this->id,
            'sender_id' => $sender?->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    /**
     * Check if user has role(s) - supports string or array
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->role && $this->role->name === $roles;
        }

        if (is_array($roles)) {
            return $this->role && in_array($this->role->name, $roles);
        }

        return false;
    }
}