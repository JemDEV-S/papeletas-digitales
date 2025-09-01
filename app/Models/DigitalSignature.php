<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DigitalSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_request_id',
        'user_id',
        'signature_type',
        'certificate_serial',
        'signature_hash',
        'signature_algorithm',
        'signature_timestamp',
        'signer_dn',
        'signed_at',
        'certificate_data',
        'signature_metadata',
        'document_path',
        'is_valid'
    ];

    protected $casts = [
        'signed_at' => 'datetime',
        'certificate_data' => 'array',
        'signature_metadata' => 'array',
        'is_valid' => 'boolean'
    ];

    protected $dates = [
        'signed_at'
    ];

    /**
     * Relación con la solicitud de permiso
     */
    public function permissionRequest(): BelongsTo
    {
        return $this->belongsTo(PermissionRequest::class);
    }

    /**
     * Relación con el usuario que firmó
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor para obtener el nombre del tipo de firma
     */
    public function getSignatureTypeNameAttribute(): string
    {
        $types = [
            // Nuevos tipos para FIRMA PERÚ
            'employee' => 'Firma del Empleado (FIRMA PERÚ)',
            'level1_supervisor' => 'Firma del Jefe Inmediato (FIRMA PERÚ)',
            'level2_hr' => 'Firma de RRHH (FIRMA PERÚ)',
            
            // Tipos anteriores (mantener compatibilidad)
            'onpe_employee_signature' => 'Firma del Empleado (ONPE)',
            'onpe_supervisor_signature' => 'Firma del Jefe Inmediato (ONPE)',
            'onpe_hr_signature' => 'Firma de RRHH (ONPE)',
            'internal_approval' => 'Aprobación Interna',
        ];

        return $types[$this->signature_type] ?? 'Tipo Desconocido';
    }

    /**
     * Accessor para verificar si el documento firmado existe
     */
    public function getDocumentExistsAttribute(): bool
    {
        return $this->document_path && Storage::exists($this->document_path);
    }

    /**
     * Accessor para obtener la URL de descarga del documento
     */
    public function getDocumentUrlAttribute(): ?string
    {
        if (!$this->document_exists) {
            return null;
        }

        return route('permissions.download-signed-pdf', $this->permission_request_id);
    }

    /**
     * Accessor para obtener el tamaño del documento firmado
     */
    public function getDocumentSizeAttribute(): ?int
    {
        if (!$this->document_exists) {
            return null;
        }

        try {
            return Storage::size($this->document_path);
        } catch (\Exception $e) {
            Log::error('Error al obtener tamaño de documento firmado', [
                'signature_id' => $this->id,
                'document_path' => $this->document_path,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Accessor para obtener el tamaño del documento formateado
     */
    public function getFormattedDocumentSizeAttribute(): ?string
    {
        $size = $this->document_size;
        
        if (!$size) {
            return null;
        }

        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / 1048576, 2) . ' MB';
        }
    }

    /**
     * Verificar la integridad del documento firmado
     */
    public function verifyDocumentIntegrity(): bool
    {
        if (!$this->document_exists) {
            return false;
        }

        try {
            $currentHash = hash_file('sha256', Storage::path($this->document_path));
            return $currentHash === $this->signature_hash;
        } catch (\Exception $e) {
            Log::error('Error al verificar integridad del documento', [
                'signature_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener información completa de la firma
     */
    public function getSignatureInfo(): array
    {
        return [
            'signature_type' => $this->signature_type_name,
            'signer' => $this->user->full_name ?? 'Usuario desconocido',
            'signer_dni' => $this->user->dni ?? 'N/A',
            'certificate_serial' => $this->certificate_serial,
            'signature_algorithm' => $this->signature_algorithm,
            'signed_at' => $this->signed_at,
            'signature_timestamp' => $this->signature_timestamp,
            'signer_dn' => $this->signer_dn,
            'document_hash' => $this->signature_hash,
            'is_valid' => $this->is_valid,
            'document_exists' => $this->document_exists,
            'document_size' => $this->formatted_document_size,
            'integrity_check' => $this->verifyDocumentIntegrity()
        ];
    }

    /**
     * Scope para firmas válidas
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true);
    }

    /**
     * Scope para firmas inválidas
     */
    public function scopeInvalid($query)
    {
        return $query->where('is_valid', false);
    }

    /**
     * Scope para firmas por tipo
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('signature_type', $type);
    }

    /**
     * Scope para firmas de empleados
     */
    public function scopeEmployeeSignatures($query)
    {
        return $query->where('signature_type', 'onpe_employee_signature');
    }

    /**
     * Scope para firmas de supervisores
     */
    public function scopeSupervisorSignatures($query)
    {
        return $query->where('signature_type', 'onpe_supervisor_signature');
    }

    /**
     * Scope para firmas de RRHH
     */
    public function scopeHrSignatures($query)
    {
        return $query->where('signature_type', 'onpe_hr_signature');
    }

    /**
     * Scope para firmas de empleado FIRMA PERÚ
     */
    public function scopeFirmaPeruEmployeeSignatures($query)
    {
        return $query->where('signature_type', 'employee');
    }

    /**
     * Scope para firmas de supervisor nivel 1 FIRMA PERÚ
     */
    public function scopeFirmaPeruLevel1Signatures($query)
    {
        return $query->where('signature_type', 'level1_supervisor');
    }

    /**
     * Scope para firmas de RRHH FIRMA PERÚ
     */
    public function scopeFirmaPeruLevel2Signatures($query)
    {
        return $query->where('signature_type', 'level2_hr');
    }

    /**
     * Scope para todas las firmas FIRMA PERÚ
     */
    public function scopeFirmaPeruSignatures($query)
    {
        return $query->whereIn('signature_type', ['employee', 'level1_supervisor', 'level2_hr']);
    }

    /**
     * Scope para firmas recientes (últimos 30 días)
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('signed_at', '>=', now()->subDays($days));
    }

    /**
     * Scope para firmas por rango de fechas
     */
    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('signed_at', [$startDate, $endDate]);
    }

    /**
     * Obtener el certificado en formato legible
     */
    public function getCertificateInfo(): array
    {
        $info = [];

        if ($this->certificate_data) {
            $info = $this->certificate_data;
        }

        if ($this->signer_dn) {
            $info['signer_dn'] = $this->signer_dn;
        }

        if ($this->certificate_serial) {
            $info['serial_number'] = $this->certificate_serial;
        }

        if ($this->signature_algorithm) {
            $info['algorithm'] = $this->signature_algorithm;
        }

        return $info;
    }

    /**
     * Marcar firma como inválida
     */
    public function markAsInvalid(string $reason = null): bool
    {
        try {
            $metadata = $this->signature_metadata ?? [];
            $metadata['invalidated_at'] = now()->toISOString();
            $metadata['invalidation_reason'] = $reason;

            return $this->update([
                'is_valid' => false,
                'signature_metadata' => $metadata
            ]);
        } catch (\Exception $e) {
            Log::error('Error al invalidar firma digital', [
                'signature_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Restaurar validez de la firma
     */
    public function markAsValid(): bool
    {
        try {
            $metadata = $this->signature_metadata ?? [];
            $metadata['restored_at'] = now()->toISOString();

            return $this->update([
                'is_valid' => true,
                'signature_metadata' => $metadata
            ]);
        } catch (\Exception $e) {
            Log::error('Error al restaurar validez de firma digital', [
                'signature_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Obtener todas las firmas de una solicitud
     */
    public static function getRequestSignatures(int $permissionRequestId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('permission_request_id', $permissionRequestId)
            ->with('user')
            ->orderBy('signed_at')
            ->get();
    }

    /**
     * Verificar si una solicitud tiene firma válida del empleado
     */
    public static function hasValidEmployeeSignature(int $permissionRequestId): bool
    {
        return self::where('permission_request_id', $permissionRequestId)
            ->whereIn('signature_type', ['onpe_employee_signature', 'employee'])
            ->where('is_valid', true)
            ->exists();
    }

    /**
     * Verificar si una solicitud tiene firma válida del empleado FIRMA PERÚ
     */
    public static function hasValidFirmaPeruEmployeeSignature(int $permissionRequestId): bool
    {
        return self::where('permission_request_id', $permissionRequestId)
            ->where('signature_type', 'employee')
            ->where('is_valid', true)
            ->exists();
    }

    /**
     * Verificar si una solicitud tiene firma válida de nivel 1 FIRMA PERÚ
     */
    public static function hasValidFirmaPeruLevel1Signature(int $permissionRequestId): bool
    {
        return self::where('permission_request_id', $permissionRequestId)
            ->where('signature_type', 'level1_supervisor')
            ->where('is_valid', true)
            ->exists();
    }

    /**
     * Verificar si una solicitud tiene firma válida de nivel 2 FIRMA PERÚ
     */
    public static function hasValidFirmaPeruLevel2Signature(int $permissionRequestId): bool
    {
        return self::where('permission_request_id', $permissionRequestId)
            ->where('signature_type', 'level2_hr')
            ->where('is_valid', true)
            ->exists();
    }

    /**
     * Verificar si una solicitud está completamente firmada
     */
    public static function isRequestFullySigned(int $permissionRequestId): bool
    {
        $employeeSigned = self::hasValidEmployeeSignature($permissionRequestId);
        
        // Para estar completamente firmada, al menos debe tener la firma del empleado
        // Aquí puedes agregar lógica adicional según tus requerimientos
        return $employeeSigned;
    }

    /**
     * Obtener estadísticas de firmas digitales
     */
    public static function getSignatureStats(): array
    {
        return [
            'total_signatures' => self::count(),
            'valid_signatures' => self::valid()->count(),
            'invalid_signatures' => self::invalid()->count(),
            'employee_signatures' => self::employeeSignatures()->count(),
            'recent_signatures' => self::recent()->count(),
            'signatures_this_month' => self::whereMonth('signed_at', now()->month)
                                           ->whereYear('signed_at', now()->year)
                                           ->count(),
        ];
    }

    /**
     * Limpiar firmas antiguas inválidas (para mantenimiento)
     */
    public static function cleanupOldInvalidSignatures(int $daysOld = 365): int
    {
        try {
            $cutoffDate = now()->subDays($daysOld);
            
            $signatures = self::invalid()
                ->where('created_at', '<', $cutoffDate)
                ->get();

            $deletedCount = 0;
            foreach ($signatures as $signature) {
                // Eliminar archivo si existe
                if ($signature->document_path && Storage::exists($signature->document_path)) {
                    Storage::delete($signature->document_path);
                }
                
                $signature->delete();
                $deletedCount++;
            }

            return $deletedCount;
        } catch (\Exception $e) {
            Log::error('Error al limpiar firmas digitales antiguas', [
                'error' => $e->getMessage()
            ]);
            return 0;
        }
    }

    /**
     * Boot method para eventos del modelo
     */
    protected static function boot()
    {
        parent::boot();

        // Log cuando se crea una nueva firma
        static::created(function ($signature) {
            Log::info('Nueva firma digital creada', [
                'signature_id' => $signature->id,
                'permission_request_id' => $signature->permission_request_id,
                'user_id' => $signature->user_id,
                'signature_type' => $signature->signature_type
            ]);
        });

        // Log cuando se marca como inválida
        static::updated(function ($signature) {
            if ($signature->isDirty('is_valid') && !$signature->is_valid) {
                Log::warning('Firma digital marcada como inválida', [
                    'signature_id' => $signature->id,
                    'permission_request_id' => $signature->permission_request_id,
                    'user_id' => $signature->user_id
                ]);
            }
        });

        // Limpiar archivo al eliminar firma
        static::deleting(function ($signature) {
            if ($signature->document_path && Storage::exists($signature->document_path)) {
                Storage::delete($signature->document_path);
            }
        });
    }
}