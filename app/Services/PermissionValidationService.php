<?php

namespace App\Services;

use App\Models\User;
use App\Models\PermissionType;
use App\Models\PermissionRequest;
use Carbon\Carbon;

class PermissionValidationService
{
    /**
     * Validar una solicitud de permiso completa
     */
    public function validatePermissionRequest(
        User $user,
        PermissionType $permissionType,
        Carbon $startDateTime,
        Carbon $endDateTime,
        ?array $documents = null,
        ?PermissionRequest $excludeRequest = null
    ): array {
        $errors = [];
        $requestedHours = $endDateTime->diffInMinutes($startDateTime) / 60;

        // Validaciones generales
        $errors = array_merge($errors, $this->validateGeneralRules($startDateTime, $endDateTime));
        
        // Validaciones por tipo de permiso
        $errors = array_merge($errors, $this->validatePermissionTypeRules($permissionType, $startDateTime, $endDateTime));
        
        // Validaciones de límites
        $errors = array_merge($errors, $this->validateLimits($user, $permissionType, $requestedHours, $startDateTime, $excludeRequest));
        
        // Validaciones de documentos
        $errors = array_merge($errors, $this->validateRequiredDocuments($permissionType, $documents));
        
        // Validaciones específicas por código de permiso
        $errors = array_merge($errors, $this->validateSpecificPermissionRules($user, $permissionType, $startDateTime, $endDateTime, $excludeRequest));

        return $errors;
    }

    /**
     * Validaciones generales aplicables a todos los permisos
     */
    private function validateGeneralRules(Carbon $startDateTime, Carbon $endDateTime): array
    {
        $errors = [];

        // No se puede solicitar permiso en horario no laboral (para ciertos tipos)
        if ($startDateTime->hour < 8 || $startDateTime->hour > 18) {
            $errors[] = 'Los permisos deben solicitarse dentro del horario laboral (8:00 AM - 6:00 PM).';
        }

        // No más de 8 horas en un día
        $hoursRequested = $endDateTime->diffInMinutes($startDateTime) / 60;
        if ($hoursRequested > 8) {
            $errors[] = 'No se pueden solicitar más de 8 horas de permiso en un día.';
        }

        return $errors;
    }

    /**
     * Validaciones por tipo de permiso
     */
    private function validatePermissionTypeRules(PermissionType $permissionType, Carbon $startDateTime, Carbon $endDateTime): array
    {
        $errors = [];

        // Validar si el permiso debe ser en el mismo día
        $sameDayRequired = [
            PermissionType::ASUNTOS_PARTICULARES,
            PermissionType::ENFERMEDAD,
            PermissionType::LACTANCIA
        ];

        if (in_array($permissionType->code, $sameDayRequired) && !$startDateTime->isSameDay($endDateTime)) {
            $errors[] = 'Este tipo de permiso debe ser solicitado para el mismo día.';
        }

        // Validar días de la semana permitidos
        if (in_array($permissionType->code, [PermissionType::DOCENCIA, PermissionType::ESTUDIOS])) {
            if (in_array($startDateTime->dayOfWeek, [Carbon::SATURDAY, Carbon::SUNDAY])) {
                $errors[] = 'Los permisos de docencia y estudios no se pueden solicitar en fines de semana.';
            }
        }

        return $errors;
    }

    /**
     * Validar límites de horas y frecuencia
     */
    private function validateLimits(
        User $user,
        PermissionType $permissionType,
        float $requestedHours,
        Carbon $startDateTime,
        ?PermissionRequest $excludeRequest = null
    ): array {
        $errors = [];

        // Validar límite diario
        $errors = array_merge($errors, $this->validateDailyLimit($permissionType, $requestedHours));
        
        // Validar límite mensual
        $errors = array_merge($errors, $this->validateMonthlyLimit($user, $permissionType, $requestedHours, $startDateTime, $excludeRequest));
        
        // Validar límite de frecuencia mensual
        $errors = array_merge($errors, $this->validateFrequencyLimit($user, $permissionType, $startDateTime, $excludeRequest));
        
        // Validar límite semanal (para docencia)
        if ($permissionType->code === PermissionType::DOCENCIA) {
            $errors = array_merge($errors, $this->validateWeeklyLimit($user, $permissionType, $requestedHours, $startDateTime, $excludeRequest));
        }

        // Validar límite anual (para representación sindical)
        if ($permissionType->code === PermissionType::SINDICAL) {
            $errors = array_merge($errors, $this->validateYearlyLimit($user, $permissionType, $requestedHours, $startDateTime, $excludeRequest));
        }

        return $errors;
    }

    /**
     * Validar límite diario
     */
    private function validateDailyLimit(PermissionType $permissionType, float $requestedHours): array
    {
        $errors = [];

        if ($permissionType->hasDailyLimit() && $requestedHours > $permissionType->max_hours_per_day) {
            $errors[] = "Excede el límite diario de {$permissionType->max_hours_per_day} horas para este tipo de permiso.";
        }

        return $errors;
    }

    /**
     * Validar límite mensual
     */
    private function validateMonthlyLimit(
        User $user,
        PermissionType $permissionType,
        float $requestedHours,
        Carbon $startDateTime,
        ?PermissionRequest $excludeRequest = null
    ): array {
        $errors = [];

        if (!$permissionType->hasMonthlyLimit()) {
            return $errors;
        }

        $query = $user->permissionRequests()
            ->where('permission_type_id', $permissionType->id)
            ->whereYear('start_datetime', $startDateTime->year)
            ->whereMonth('start_datetime', $startDateTime->month)
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($excludeRequest) {
            $query->where('id', '!=', $excludeRequest->id);
        }

        $monthlyHours = $query->sum('requested_hours');

        if (($monthlyHours + $requestedHours) > $permissionType->max_hours_per_month) {
            $remaining = max(0, $permissionType->max_hours_per_month - $monthlyHours);
            $errors[] = "Excede el límite mensual de {$permissionType->max_hours_per_month} horas. Le quedan {$remaining} horas disponibles.";
        }

        return $errors;
    }

    /**
     * Validar límite de frecuencia mensual
     */
    private function validateFrequencyLimit(
        User $user,
        PermissionType $permissionType,
        Carbon $startDateTime,
        ?PermissionRequest $excludeRequest = null
    ): array {
        $errors = [];

        if (!$permissionType->hasFrequencyLimit()) {
            return $errors;
        }

        $query = $user->permissionRequests()
            ->where('permission_type_id', $permissionType->id)
            ->whereYear('start_datetime', $startDateTime->year)
            ->whereMonth('start_datetime', $startDateTime->month)
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($excludeRequest) {
            $query->where('id', '!=', $excludeRequest->id);
        }

        $monthlyCount = $query->count();

        if ($monthlyCount >= $permissionType->max_times_per_month) {
            $errors[] = "Ya alcanzó el límite de {$permissionType->max_times_per_month} permisos de este tipo por mes.";
        }

        return $errors;
    }

    /**
     * Validar límite semanal (específico para docencia)
     */
    private function validateWeeklyLimit(
        User $user,
        PermissionType $permissionType,
        float $requestedHours,
        Carbon $startDateTime,
        ?PermissionRequest $excludeRequest = null
    ): array {
        $errors = [];

        $weekStart = $startDateTime->copy()->startOfWeek();
        $weekEnd = $startDateTime->copy()->endOfWeek();

        $query = $user->permissionRequests()
            ->where('permission_type_id', $permissionType->id)
            ->whereBetween('start_datetime', [$weekStart, $weekEnd])
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($excludeRequest) {
            $query->where('id', '!=', $excludeRequest->id);
        }

        $weeklyHours = $query->sum('requested_hours');

        if (($weeklyHours + $requestedHours) > 6) { // Máximo 6 horas semanales según normativa
            $remaining = max(0, 6 - $weeklyHours);
            $errors[] = "Excede el límite semanal de 6 horas para docencia universitaria. Le quedan {$remaining} horas esta semana.";
        }

        return $errors;
    }

    /**
     * Validar límite anual (específico para representación sindical)
     */
    private function validateYearlyLimit(
        User $user,
        PermissionType $permissionType,
        float $requestedHours,
        Carbon $startDateTime,
        ?PermissionRequest $excludeRequest = null
    ): array {
        $errors = [];

        $query = $user->permissionRequests()
            ->where('permission_type_id', $permissionType->id)
            ->whereYear('start_datetime', $startDateTime->year)
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($excludeRequest) {
            $query->where('id', '!=', $excludeRequest->id);
        }

        $yearlyHours = $query->sum('requested_hours');
        $maxYearlyHours = 30 * 8; // 30 días * 8 horas = 240 horas por año

        if (($yearlyHours + $requestedHours) > $maxYearlyHours) {
            $remaining = max(0, $maxYearlyHours - $yearlyHours);
            $errors[] = "Excede el límite anual de 30 días (240 horas) para representación sindical. Le quedan {$remaining} horas este año.";
        }

        return $errors;
    }

    /**
     * Validar documentos requeridos
     */
    private function validateRequiredDocuments(PermissionType $permissionType, ?array $documents = null): array
    {
        $errors = [];
        $requiredDocs = $permissionType->getRequiredDocuments();

        if (empty($requiredDocs)) {
            return $errors;
        }

        $providedDocs = $documents ?? [];
        $missingDocs = array_diff($requiredDocs, $providedDocs);

        if (!empty($missingDocs)) {
            $docNames = $this->getDocumentNames($missingDocs);
            $errors[] = 'Faltan documentos requeridos: ' . implode(', ', $docNames);
        }

        return $errors;
    }

    /**
     * Validaciones específicas por código de permiso
     */
    private function validateSpecificPermissionRules(
        User $user,
        PermissionType $permissionType,
        Carbon $startDateTime,
        Carbon $endDateTime,
        ?PermissionRequest $excludeRequest = null
    ): array {
        $errors = [];

        switch ($permissionType->code) {
            case PermissionType::LACTANCIA:
                $errors = array_merge($errors, $this->validateLactanciaRules($startDateTime, $endDateTime));
                break;

            case PermissionType::GRAVIDEZ:
                $errors = array_merge($errors, $this->validateGravidezRules($user, $startDateTime, $excludeRequest));
                break;

            case PermissionType::VACACIONAL:
                $errors = array_merge($errors, $this->validateVacacionalRules($user, $startDateTime, $excludeRequest));
                break;

            case PermissionType::ASUNTOS_PARTICULARES:
                $errors = array_merge($errors, $this->validateAsuntosParticularesRules($startDateTime, $endDateTime));
                break;
        }

        return $errors;
    }

    /**
     * Validaciones específicas para lactancia
     */
    private function validateLactanciaRules(Carbon $startDateTime, Carbon $endDateTime): array
    {
        $errors = [];
        $hours = $endDateTime->diffInMinutes($startDateTime) / 60;

        if ($hours > 1) {
            $errors[] = 'El permiso por lactancia es máximo 1 hora por día.';
        }

        // Debe ser al inicio, fin o dentro de la jornada
        $validHours = [8, 12, 17]; // 8am (inicio), 12pm (almuerzo), 5pm (fin)
        if (!in_array($startDateTime->hour, $validHours) && $startDateTime->hour !== 13) {
            $errors[] = 'El permiso por lactancia debe ser al inicio, almuerzo o fin de jornada.';
        }

        return $errors;
    }

    /**
     * Validaciones específicas para gravidez
     */
    private function validateGravidezRules(User $user, Carbon $startDateTime, ?PermissionRequest $excludeRequest = null): array
    {
        $errors = [];

        // Solo una vez al mes según normativa
        $query = $user->permissionRequests()
            ->where('permission_type_id', function ($q) {
                $q->select('id')
                  ->from('permission_types')
                  ->where('code', PermissionType::GRAVIDEZ);
            })
            ->whereYear('start_datetime', $startDateTime->year)
            ->whereMonth('start_datetime', $startDateTime->month)
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($excludeRequest) {
            $query->where('id', '!=', $excludeRequest->id);
        }

        if ($query->exists()) {
            $errors[] = 'Solo se puede solicitar un permiso por gravidez al mes para control mensual.';
        }

        return $errors;
    }

    /**
     * Validaciones específicas para vacacional
     */
    private function validateVacacionalRules(User $user, Carbon $startDateTime, ?PermissionRequest $excludeRequest = null): array
    {
        $errors = [];

        // Máximo 3 veces por mes según normativa
        $query = $user->permissionRequests()
            ->where('permission_type_id', function ($q) {
                $q->select('id')
                  ->from('permission_types')
                  ->where('code', PermissionType::VACACIONAL);
            })
            ->whereYear('start_datetime', $startDateTime->year)
            ->whereMonth('start_datetime', $startDateTime->month)
            ->whereNotIn('status', ['rejected', 'cancelled']);

        if ($excludeRequest) {
            $query->where('id', '!=', $excludeRequest->id);
        }

        if ($query->count() >= 3) {
            $errors[] = 'Solo se pueden solicitar 3 permisos a cuenta del período vacacional por mes.';
        }

        return $errors;
    }

    /**
     * Validaciones específicas para asuntos particulares
     */
    private function validateAsuntosParticularesRules(Carbon $startDateTime, Carbon $endDateTime): array
    {
        $errors = [];
        $hours = $endDateTime->diffInMinutes($startDateTime) / 60;

        // Máximo 2 horas por día según normativa
        if ($hours > 2) {
            $errors[] = 'Los permisos por asuntos particulares son máximo 2 horas por día.';
        }

        return $errors;
    }

    /**
     * Obtener nombres legibles de documentos
     */
    private function getDocumentNames(array $docTypes): array
    {
        $names = [
            'certificado_medico' => 'Certificado médico',
            'citacion' => 'Copia de citación',
            'acreditacion' => 'Acreditación',
            'resolucion_nombramiento' => 'Resolución de nombramiento',
            'horario_ensenanza' => 'Horario de enseñanza',
            'horario_recuperacion' => 'Horario de recuperación',
            'partida_nacimiento' => 'Partida de nacimiento',
            'declaracion_jurada' => 'Declaración jurada',
        ];

        return array_map(fn($type) => $names[$type] ?? $type, $docTypes);
    }

    /**
     * Verificar si un usuario puede solicitar un tipo específico de permiso
     */
    public function canUserRequestPermissionType(User $user, PermissionType $permissionType): array
    {
        $errors = [];

        // Verificaciones específicas por tipo de usuario o condiciones
        switch ($permissionType->code) {
            case PermissionType::LACTANCIA:
                // Solo mujeres pueden solicitar permiso de lactancia
                // Esta validación debería basarse en género o condición especial del usuario
                break;

            case PermissionType::FUNCION_EDIL:
                // Solo usuarios con función edil pueden solicitar este permiso
                // Verificar si tiene rol o atributo especial
                break;

            case PermissionType::SINDICAL:
                // Solo representantes sindicales pueden solicitar este permiso
                if (!$user->hasRole('representante_sindical')) {
                    $errors[] = 'Solo los representantes sindicales pueden solicitar este tipo de permiso.';
                }
                break;
        }

        return $errors;
    }
}