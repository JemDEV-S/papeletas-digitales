<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PermissionTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_request_id',
        'employee_dni',
        'departure_datetime',
        'return_datetime',
        'actual_hours_used',
        'tracking_status',
        'registered_by_user_id',
        'notes',
    ];

    protected $casts = [
        'departure_datetime' => 'datetime',
        'return_datetime' => 'datetime',
        'actual_hours_used' => 'decimal:2',
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_OUT = 'out';
    const STATUS_RETURNED = 'returned';
    const STATUS_OVERDUE = 'overdue';

    protected static function boot()
    {
        parent::boot();

        static::updating(function ($tracking) {
            if ($tracking->isDirty(['departure_datetime', 'return_datetime'])) {
                $tracking->calculateActualHours();
            }
        });
    }

    public function permissionRequest()
    {
        return $this->belongsTo(PermissionRequest::class);
    }

    public function registeredByUser()
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function employee()
    {
        return $this->hasOneThrough(
            User::class,
            PermissionRequest::class,
            'id',
            'id',
            'permission_request_id',
            'user_id'
        );
    }

    public function registerDeparture(User $registeredBy, ?string $notes = null): bool
    {
        if ($this->tracking_status !== self::STATUS_PENDING) {
            return false;
        }

        $this->departure_datetime = now();
        $this->tracking_status = self::STATUS_OUT;
        $this->registered_by_user_id = $registeredBy->id;
        if ($notes) {
            $this->notes = $notes;
        }

        return $this->save();
    }

    public function registerReturn(User $registeredBy, ?string $notes = null): bool
    {
        if ($this->tracking_status !== self::STATUS_OUT) {
            return false;
        }

        $this->return_datetime = now();
        $this->tracking_status = self::STATUS_RETURNED;
        $this->registered_by_user_id = $registeredBy->id;
        $this->calculateActualHours();
        
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . $notes;
        }

        return $this->save();
    }

    public function calculateActualHours(): void
    {
        if ($this->departure_datetime && $this->return_datetime) {
            $departure = Carbon::parse($this->departure_datetime);
            $return = Carbon::parse($this->return_datetime);
            $this->actual_hours_used = round($departure->diffInMinutes($return) / 60, 2);
        }
    }

    public function markAsOverdue(): bool
    {
        if ($this->tracking_status === self::STATUS_OUT && $this->isOverdue()) {
            $this->tracking_status = self::STATUS_OVERDUE;
            return $this->save();
        }
        return false;
    }

    public function isOverdue(): bool
    {
        if (!$this->departure_datetime || $this->return_datetime) {
            return false;
        }

        $expectedReturn = Carbon::parse($this->departure_datetime)
            ->addHours(8); // Default 8 hours - will be configurable per permission type
        
        return now()->greaterThan($expectedReturn->addHour()); // Grace period of 1 hour
    }

    public function getStatusLabel(): string
    {
        return match($this->tracking_status) {
            self::STATUS_PENDING => 'Pendiente de Salida',
            self::STATUS_OUT => 'Fuera de Oficina',
            self::STATUS_RETURNED => 'Ha Regresado',
            self::STATUS_OVERDUE => 'Retraso en Regreso',
            default => 'Desconocido',
        };
    }

    public function getStatusColor(): string
    {
        return match($this->tracking_status) {
            self::STATUS_PENDING => 'blue',
            self::STATUS_OUT => 'yellow',
            self::STATUS_RETURNED => 'green',
            self::STATUS_OVERDUE => 'red',
            default => 'gray',
        };
    }

    public function scopePendingDeparture($query)
    {
        return $query->where('tracking_status', self::STATUS_PENDING);
    }

    public function scopeCurrentlyOut($query)
    {
        return $query->where('tracking_status', self::STATUS_OUT);
    }

    public function scopeOverdue($query)
    {
        return $query->where('tracking_status', self::STATUS_OVERDUE);
    }

    public function scopeForDni($query, string $dni)
    {
        return $query->where('employee_dni', $dni);
    }
}
