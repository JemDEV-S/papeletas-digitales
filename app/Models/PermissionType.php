<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermissionType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_hours_per_day',
        'max_hours_per_month',
        'max_times_per_month',
        'requires_document',
        'with_pay',
        'validation_rules',
        'is_active',
    ];

    protected $casts = [
        'requires_document' => 'boolean',
        'with_pay' => 'boolean',
        'is_active' => 'boolean',
        'validation_rules' => 'array',
    ];

    /**
     * Constants for permission type codes
     */
    const ENFERMEDAD = 'enfermedad';
    const GRAVIDEZ = 'gravidez';
    const CAPACITACION = 'capacitacion';
    const CITACION = 'citacion';
    const FUNCION_EDIL = 'funcion_edil';
    const VACACIONAL = 'vacacional';
    const REPRESENTACION = 'representacion';
    const DOCENCIA = 'docencia';
    const ESTUDIOS = 'estudios';
    const SINDICAL = 'sindical';
    const LACTANCIA = 'lactancia';
    const COMISION = 'comision';
    const ASUNTOS_PARTICULARES = 'asuntos_particulares';
    const OTROS = 'otros';

    /**
     * Get icon emoji for the permission type
     */
    public function getIconEmoji(): string
    {
        return match($this->code) {
            self::ENFERMEDAD => '🏥',
            self::GRAVIDEZ => '🤱',
            self::CAPACITACION => '📚',
            self::CITACION => '⚖️',
            self::FUNCION_EDIL => '🏛️',
            self::VACACIONAL => '🏖️',
            self::REPRESENTACION => '🎭',
            self::DOCENCIA => '👨‍🏫',
            self::ESTUDIOS => '🎓',
            self::SINDICAL => '🤝',
            self::LACTANCIA => '🍼',
            self::COMISION => '📋',
            self::ASUNTOS_PARTICULARES => '👤',
            default => '📄',
        };
    }

    /**
     * Get all permission requests of this type
     */
    public function permissionRequests()
    {
        return $this->hasMany(PermissionRequest::class);
    }

    /**
     * Scope for active permission types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for permission types with pay
     */
    public function scopeWithPay($query)
    {
        return $query->where('with_pay', true);
    }

    /**
     * Check if this permission type has daily limit
     */
    public function hasDailyLimit(): bool
    {
        return $this->max_hours_per_day !== null && $this->max_hours_per_day > 0;
    }

    /**
     * Check if this permission type has monthly limit
     */
    public function hasMonthlyLimit(): bool
    {
        return $this->max_hours_per_month !== null && $this->max_hours_per_month > 0;
    }

    /**
     * Check if this permission type has frequency limit
     */
    public function hasFrequencyLimit(): bool
    {
        return $this->max_times_per_month !== null && $this->max_times_per_month > 0;
    }

    /**
     * Get required documents based on permission type
     */
    public function getRequiredDocuments(): array
    {
        // Los documentos ahora son opcionales para todos los tipos de permisos
        // El usuario puede subirlos cuando quiera o cuando los tenga disponibles
        return [];
    }

    /**
     * Get recommended documents based on permission type (for informational purposes)
     */
    public function getRecommendedDocuments(): array
    {
        $documents = [];

        switch ($this->code) {
            case self::ENFERMEDAD:
                $documents[] = 'certificado_medico';
                break;
            case self::GRAVIDEZ:
                $documents[] = 'certificado_medico';
                break;
            case self::CITACION:
                $documents[] = 'citacion';
                break;
            case self::FUNCION_EDIL:
                $documents[] = 'acreditacion';
                break;
            case self::DOCENCIA:
                $documents[] = 'resolucion_nombramiento';
                $documents[] = 'horario_ensenanza';
                break;
            case self::ESTUDIOS:
                $documents[] = 'resolucion_nombramiento';
                $documents[] = 'horario_recuperacion';
                break;
            case self::LACTANCIA:
                $documents[] = 'partida_nacimiento';
                $documents[] = 'declaracion_jurada';
                break;
        }

        return $documents;
    }

    /**
     * Validate if hours requested are within limits
     */
    public function validateHours(float $hours, ?string $date = null): array
    {
        $errors = [];

        // Validar límite diario
        if ($this->hasDailyLimit() && $hours > $this->max_hours_per_day) {
            $errors[] = "Las horas solicitadas ({$hours}) exceden el límite diario de {$this->max_hours_per_day} horas.";
        }

        return $errors;
    }

    /**
     * Get permission type by code
     */
    public static function getByCode(string $code)
    {
        return static::where('code', $code)->first();
    }
}