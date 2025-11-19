<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App\Events\PermissionRequestSubmitted;
use App\Events\PermissionRequestApproved;
use App\Events\PermissionRequestRejected;
use App\Jobs\ProcessPermissionStatusChange;

class PermissionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'user_id',
        'permission_type_id',
        'reason',
        'status',
        'metadata',
        'submitted_at',
        'current_approval_level',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Status constants
     */
    const STATUS_DRAFT = 'draft';
    const STATUS_PENDING_IMMEDIATE_BOSS = 'pending_immediate_boss';
    const STATUS_PENDING_HR = 'pending_hr';
    const STATUS_APPROVED = 'approved';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_REJECTED = 'rejected';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($request) {
            // Generate request number
            $request->request_number = static::generateRequestNumber();
        });
    }

    /**
     * Generate unique request number
     */
    public static function generateRequestNumber(): string
    {
        $year = date('Y');
        $lastRequest = static::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastRequest ? intval(substr($lastRequest->request_number, -6)) + 1 : 1;
        
        return sprintf('PAP-%s-%06d', $year, $sequence);
    }


    /**
     * Create tracking record after approval
     */
    protected function createTrackingRecord(): void
    {
        if (!$this->tracking) {
            $this->tracking()->create([
                'employee_dni' => $this->user->dni,
                'tracking_status' => PermissionTracking::STATUS_PENDING,
            ]);
        }
    }

    /**
     * Check if permission is currently being tracked
     */
    public function hasActiveTracking(): bool
    {
        return $this->tracking && 
               in_array($this->tracking->tracking_status, [
                   PermissionTracking::STATUS_PENDING,
                   PermissionTracking::STATUS_OUT
               ]);
    }

    /**
     * Get tracking status
     */
    public function getTrackingStatus(): ?string
    {
        return $this->tracking ? $this->tracking->tracking_status : null;
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permissionType()
    {
        return $this->belongsTo(PermissionType::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function digitalSignatures()
    {
        return $this->hasMany(DigitalSignature::class);
    }

    public function tracking()
    {
        return $this->hasOne(PermissionTracking::class);
    }

    /**
     * Get current approval
     */
    public function currentApproval()
    {
        return $this->approvals()
            ->where('approval_level', $this->current_approval_level)
            ->first();
    }

    /**
     * Get approval by level
     */
    public function getApprovalByLevel(int $level)
    {
        return $this->approvals()
            ->where('approval_level', $level)
            ->first();
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING_IMMEDIATE_BOSS,
            self::STATUS_PENDING_HR
        ]);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('created_at', $year)
            ->whereMonth('created_at', $month);
    }

    /**
     * Check if request is editable
     */
    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    /**
     * Check if documents can be uploaded (always enabled until 24 hours after approval)
     */
    public function canUploadDocuments(): bool
    {
        // Si el estado es aprobado, verificar si han pasado 24 horas desde la aprobación
        if ($this->status === self::STATUS_APPROVED) {
            $finalApprovalDate = $this->getFinalApprovalDate();
            if ($finalApprovalDate) {
                $twentyFourHoursAfterApproval = $finalApprovalDate->copy()->addHours(24);
                return now()->lt($twentyFourHoursAfterApproval);
            }
        }

        // Para todos los otros estados (draft, pending_immediate_boss, pending_hr, etc.)
        // siempre permitir subir documentos
        return !in_array($this->status, [self::STATUS_REJECTED, self::STATUS_CANCELLED]);
    }

    /**
     * Get the date when the permission was finally approved
     */
    public function getFinalApprovalDate(): ?Carbon
    {
        // Obtener la última aprobación (nivel más alto)
        $finalApproval = $this->approvals()
            ->where('status', 'approved')
            ->orderBy('approval_level', 'desc')
            ->first();

        return $finalApproval ? $finalApproval->approved_at : null;
    }

    /**
     * Check if request can be submitted
     */
    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT && 
               $this->hasEmployeeFirmaPeruSignature();
    }

    /**
     * Check if request has all required documents
     */
    public function hasRequiredDocuments(): bool
    {
        $requiredDocs = $this->permissionType->getRequiredDocuments();
        
        if (empty($requiredDocs)) {
            return true;
        }

        $uploadedTypes = $this->documents->pluck('document_type')->toArray();
        
        return count(array_diff($requiredDocs, $uploadedTypes)) === 0;
    }

    /**
     * Submit the request
     */
    public function submit(): bool
    {
        if (!$this->canBeSubmitted()) {
            return false;
        }

        $this->status = self::STATUS_PENDING_IMMEDIATE_BOSS;
        $this->submitted_at = now();
        $this->current_approval_level = 1;
        
        // Create first approval record
        $this->approvals()->create([
            'approver_id' => $this->user->immediate_supervisor_id,
            'approval_level' => 1,
            'status' => 'pending',
        ]);

        $saved = $this->save();
        
        if ($saved) {
            // Disparar evento de envío
            $approver = User::find($this->user->immediate_supervisor_id);
            if ($approver) {
                event(new PermissionRequestSubmitted($this, $approver));
            }
        }

        return $saved;
    }

    /**
     * Approve the request at current level
     */
    public function approve(User $approver, ?string $comments = null): bool
    {
        $approval = $this->currentApproval();
        
        if (!$approval || $approval->approver_id !== $approver->id) {
            return false;
        }

        $approval->status = 'approved';
        $approval->comments = $comments;
        $approval->approved_at = now();
        $approval->save();

        $previousStatus = $this->status;
        $nextApprover = null;
        
        // Check if needs HR approval
        if ($this->current_approval_level === 1) {
            // Move to HR approval
            $this->status = self::STATUS_PENDING_HR;
            $this->current_approval_level = 2;
            
            // Create HR approval record
            $hrChief = User::whereHas('role', function ($q) {
                $q->where('name', 'jefe_rrhh');
            })->first();

            if ($hrChief) {
                $this->approvals()->create([
                    'approver_id' => $hrChief->id,
                    'approval_level' => 2,
                    'status' => 'pending',
                ]);
                $nextApprover = $hrChief;
            }
        } else {
            // Final approval - create tracking record
            $this->status = self::STATUS_APPROVED;
            $this->createTrackingRecord();
        }

        $saved = $this->save();
        
        if ($saved) {
            // Disparar evento de aprobación
            event(new PermissionRequestApproved($this, $approver, $nextApprover));
            
            // Procesar cambio de estado
            ProcessPermissionStatusChange::dispatch($this->id, $previousStatus, $this->status);
        }

        return $saved;
    }

    /**
     * Reject the request
     */
    public function reject(User $approver, string $comments): bool
    {
        $approval = $this->currentApproval();
        
        if (!$approval || $approval->approver_id !== $approver->id) {
            return false;
        }

        $approval->status = 'rejected';
        $approval->comments = $comments;
        $approval->approved_at = now();
        $approval->save();

        $previousStatus = $this->status;
        $this->status = self::STATUS_REJECTED;
        
        $saved = $this->save();
        
        if ($saved) {
            // Disparar evento de rechazo
            event(new PermissionRequestRejected($this, $approver, $comments));
            
            // Procesar cambio de estado
            ProcessPermissionStatusChange::dispatch($this->id, $previousStatus, $this->status);
        }
        
        return $saved;
    }

    /**
     * Cancel the request
     */
    public function cancel(): bool
    {
        if (!in_array($this->status, [self::STATUS_DRAFT, self::STATUS_PENDING_IMMEDIATE_BOSS])) {
            return false;
        }

        $this->status = self::STATUS_CANCELLED;
        
        return $this->save();
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_PENDING_IMMEDIATE_BOSS => 'Pendiente Jefe Inmediato',
            self::STATUS_PENDING_HR => 'Pendiente RRHH',
            self::STATUS_APPROVED => 'Aprobado',
            self::STATUS_REJECTED => 'Rechazado',
            self::STATUS_CANCELLED => 'Cancelado',
            default => 'Desconocido',
        };
    }

    /**
     * Get status color for UI
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_DRAFT => 'gray',
            self::STATUS_PENDING_IMMEDIATE_BOSS, self::STATUS_PENDING_HR => 'yellow',
            self::STATUS_APPROVED => 'green',
            self::STATUS_REJECTED => 'red',
            self::STATUS_CANCELLED => 'gray',
            default => 'gray',
        };
    }

    /**
     * Verificar si tiene firma válida del empleado FIRMA PERÚ
     */
    public function hasEmployeeFirmaPeruSignature(): bool
    {
        return DigitalSignature::hasValidFirmaPeruEmployeeSignature($this->id);
    }

    /**
     * Verificar si tiene firma válida de nivel 1 FIRMA PERÚ
     */
    public function hasLevel1FirmaPeruSignature(): bool
    {
        return DigitalSignature::hasValidFirmaPeruLevel1Signature($this->id);
    }

    /**
     * Verificar si tiene firma válida de nivel 2 FIRMA PERÚ
     */
    public function hasLevel2FirmaPeruSignature(): bool
    {
        return DigitalSignature::hasValidFirmaPeruLevel2Signature($this->id);
    }

    /**
     * Verificar si está completamente firmado con FIRMA PERÚ
     */
    public function isFullySignedFirmaPeru(): bool
    {
        return $this->hasEmployeeFirmaPeruSignature() && 
               $this->hasLevel1FirmaPeruSignature() && 
               $this->hasLevel2FirmaPeruSignature();
    }

    /**
     * Obtener el documento firmado más reciente
     */
    public function getLatestSignedDocument(): ?DigitalSignature
    {
        return $this->digitalSignatures()
            ->where('is_valid', true)
            ->latest('signed_at')
            ->first();
    }

    /**
     * Verificar si tiene algún documento firmado válido
     */
    public function hasSignedDocument(): bool
    {
        return $this->digitalSignatures()
            ->where('is_valid', true)
            ->exists();
    }

    /**
     * Obtener la etapa de firma actual para FIRMA PERÚ
     */
    public function getCurrentFirmaPeruStage(): int
    {
        if (!$this->hasEmployeeFirmaPeruSignature()) {
            return 1; // Necesita firma del empleado
        } elseif (!$this->hasLevel1FirmaPeruSignature()) {
            return 2; // Necesita firma de jefe inmediato
        } elseif (!$this->hasLevel2FirmaPeruSignature()) {
            return 3; // Necesita firma de RRHH
        } else {
            return 0; // Completamente firmado
        }
    }

    /**
     * Verificar si el usuario puede firmar en FIRMA PERÚ
     */
    public function canUserSignFirmaPeru(User $user): array
    {
        // Empleado puede firmar si es su solicitud y está en draft
        if ($user->id === $this->user_id && $this->status === self::STATUS_DRAFT && !$this->hasEmployeeFirmaPeruSignature()) {
            return [
                'can_sign' => true,
                'stage' => 1,
                'role' => 'employee'
            ];
        }

        // Jefe inmediato puede firmar si es el supervisor directo del solicitante
        if ($user->hasRole('jefe_inmediato') &&
            $this->user->immediate_supervisor_id === $user->id &&
            $this->status === self::STATUS_PENDING_IMMEDIATE_BOSS &&
            $this->hasEmployeeFirmaPeruSignature() &&
            !$this->hasLevel1FirmaPeruSignature()) {
            return [
                'can_sign' => true,
                'stage' => 2,
                'role' => 'level1_supervisor'
            ];
        }

        // CASO ESPECIAL: RRHH puede firmar nivel 1 si el jefe inmediato no está disponible
        $skipImmediateSupervisor = $this->metadata['skip_immediate_supervisor'] ?? false;
        if ($user->hasRole('jefe_rrhh') &&
            $skipImmediateSupervisor &&
            $this->status === self::STATUS_PENDING_IMMEDIATE_BOSS &&
            $this->current_approval_level === 1 &&
            $this->hasEmployeeFirmaPeruSignature() &&
            !$this->hasLevel1FirmaPeruSignature()) {
            return [
                'can_sign' => true,
                'stage' => 2, // Stage 2 para mostrar el botón amarillo de nivel 1
                'role' => 'level1_hr_special',
                'is_special_case' => true
            ];
        }

        // RRHH puede firmar si la solicitud está pendiente de RRHH y tiene las firmas previas
        if ($user->hasRole('jefe_rrhh') &&
            $this->status === self::STATUS_PENDING_HR &&
            $this->hasEmployeeFirmaPeruSignature() &&
            $this->hasLevel1FirmaPeruSignature() &&
            !$this->hasLevel2FirmaPeruSignature()) {
            return [
                'can_sign' => true,
                'stage' => 3,
                'role' => 'level2_hr'
            ];
        }

        return [
            'can_sign' => false,
            'stage' => 0,
            'role' => null,
            'reason' => $this->getCannotSignReason($user)
        ];
    }

    /**
     * Obtener razón por la cual no puede firmar
     */
    protected function getCannotSignReason(User $user): string
    {
        if ($user->id === $this->user_id) {
            if ($this->status !== self::STATUS_DRAFT) {
                return 'La solicitud no está en estado borrador';
            }
            if ($this->hasEmployeeFirmaPeruSignature()) {
                return 'La solicitud ya está firmada por el empleado';
            }
        } elseif ($user->hasRole('jefe_inmediato')) {
            if ($this->status !== self::STATUS_PENDING_IMMEDIATE_BOSS) {
                return 'La solicitud no está pendiente de aprobación de jefe inmediato';
            }
            if (!$this->hasEmployeeFirmaPeruSignature()) {
                return 'La solicitud debe estar firmada por el empleado primero';
            }
            if ($this->hasLevel1FirmaPeruSignature()) {
                return 'La solicitud ya está firmada por el jefe inmediato';
            }
        } elseif ($user->hasRole('jefe_rrhh')) {
            $skipImmediateSupervisor = $this->metadata['skip_immediate_supervisor'] ?? false;

            // Caso especial: RRHH puede firmar en nivel 1
            if ($skipImmediateSupervisor && $this->status === self::STATUS_PENDING_IMMEDIATE_BOSS) {
                if (!$this->hasEmployeeFirmaPeruSignature()) {
                    return 'La solicitud debe estar firmada por el empleado primero';
                }
                if ($this->hasLevel1FirmaPeruSignature()) {
                    return 'La solicitud ya está firmada para nivel 1';
                }
            }

            // Caso normal: RRHH firma en nivel 2
            if ($this->status !== self::STATUS_PENDING_HR && !$skipImmediateSupervisor) {
                return 'La solicitud no está pendiente de aprobación de RRHH';
            }
            if (!$this->hasLevel1FirmaPeruSignature() && !$skipImmediateSupervisor) {
                return 'La solicitud debe estar firmada por empleado y jefe inmediato primero';
            }
            if ($this->hasLevel2FirmaPeruSignature()) {
                return 'La solicitud ya está firmada por RRHH';
            }
        }

        return 'No tiene permisos para firmar esta solicitud';
    }

    /**
     * Obtener información del estado de firmas FIRMA PERÚ
     */
    public function getFirmaPeruSignatureStatus(): array
    {
        $signatures = $this->digitalSignatures()
            ->firmaPeruSignatures()
            ->with('user')
            ->orderBy('signed_at')
            ->get();

        return [
            'total_signatures' => $signatures->count(),
            'signatures' => $signatures->map(function ($signature) {
                return [
                    'id' => $signature->id,
                    'type' => $signature->signature_type,
                    'stage' => $this->getStageFromType($signature->signature_type),
                    'signer_name' => $signature->user->name,
                    'signer_dni' => $signature->user->dni,
                    'signed_at' => $signature->signed_at,
                    'is_valid' => $signature->is_valid,
                    'integrity_valid' => $signature->verifyDocumentIntegrity()
                ];
            }),
            'current_stage' => $this->getCurrentFirmaPeruStage(),
            'is_fully_signed' => $this->isFullySignedFirmaPeru(),
            'status' => $this->status
        ];
    }

    /**
     * Obtener número de etapa desde tipo de firma
     */
    protected function getStageFromType(string $signatureType): int
    {
        return match($signatureType) {
            'employee' => 1,
            'level1_supervisor' => 2,
            'level2_hr' => 3,
            default => 0
        };
    }

    /**
     * Verificar si tiene PDF con tracking generado
     */
    public function hasTrackingPdf(): bool
    {
        $metadata = $this->metadata ?? [];

        if (!isset($metadata['tracking_pdf_path'])) {
            return false;
        }

        $path = storage_path('app/' . $metadata['tracking_pdf_path']);
        $exists = file_exists($path);

        \Log::debug('Verificando PDF con tracking', [
            'permission_id' => $this->id,
            'metadata_path' => $metadata['tracking_pdf_path'],
            'full_path' => $path,
            'exists' => $exists
        ]);

        return $exists;
    }

    /**
     * Obtener ruta del PDF con tracking
     */
    public function getTrackingPdfPath(): ?string
    {
        $metadata = $this->metadata ?? [];

        if (isset($metadata['tracking_pdf_path'])) {
            $path = storage_path('app/' . $metadata['tracking_pdf_path']);
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Obtener fecha de generación del PDF con tracking
     */
    public function getTrackingPdfGeneratedAt(): ?\Carbon\Carbon
    {
        $metadata = $this->metadata ?? [];

        if (isset($metadata['tracking_pdf_generated_at'])) {
            return \Carbon\Carbon::parse($metadata['tracking_pdf_generated_at']);
        }

        return null;
    }
}