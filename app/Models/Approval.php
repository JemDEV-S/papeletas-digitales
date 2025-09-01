<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_request_id',
        'approver_id',
        'approval_level',
        'status',
        'comments',
        'approved_at',
        'digital_signature_hash',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'approval_level' => 'integer',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Relationships
     */
    public function permissionRequest()
    {
        return $this->belongsTo(PermissionRequest::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForApprover($query, $approverId)
    {
        return $query->where('approver_id', $approverId);
    }

    /**
     * Check if approval is pending
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if approval is approved
     */
    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Check if approval is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    /**
     * Get approval level label
     */
    public function getLevelLabel(): string
    {
        return match($this->approval_level) {
            1 => 'Jefe Inmediato',
            2 => 'Jefe de RRHH',
            default => 'Nivel ' . $this->approval_level,
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
            default => 'Desconocido',
        };
    }

    /**
     * Get status color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * Sign the approval digitally
     */
    public function sign(string $signature): void
    {
        $this->digital_signature_hash = hash('sha256', $signature . $this->id . $this->approved_at);
        $this->save();
    }

    /**
     * Verify digital signature
     */
    public function verifySignature(string $signature): bool
    {
        $expectedHash = hash('sha256', $signature . $this->id . $this->approved_at);
        return $this->digital_signature_hash === $expectedHash;
    }
}