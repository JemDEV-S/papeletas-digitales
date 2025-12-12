<?php

namespace App\Services;

use App\Models\PermissionRequest;
use App\Models\PermissionType;
use App\Models\User;
use App\Models\Document;
use App\Events\PermissionRequestSubmitted;
use App\Events\PermissionRequestApproved;
use App\Events\PermissionRequestRejected;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PermissionService
{
    /**
     * Crear una nueva solicitud de permiso
     */
    public function createPermissionRequest(User $user, array $data, array $files = []): PermissionRequest
    {
        return DB::transaction(function () use ($user, $data, $files) {
            // Preparar metadata
            $metadata = [];

            // Si se marcó que el jefe inmediato no está disponible
            if (!empty($data['skip_immediate_supervisor'])) {
                $metadata['skip_immediate_supervisor'] = true;
                $metadata['skip_reason_timestamp'] = now()->toISOString();
            }

            // Crear la solicitud
            $permissionRequest = $user->permissionRequests()->create([
                'permission_type_id' => $data['permission_type_id'],
                'reason' => $data['reason'],
                'status' => PermissionRequest::STATUS_DRAFT,
                'metadata' => $metadata,
            ]);

            // Subir documentos si existen
            if (!empty($files)) {
                $this->uploadDocuments($permissionRequest, $files, $data['document_types'] ?? []);
            }

            return $permissionRequest;
        });
    }

    /**
     * Actualizar una solicitud de permiso
     */
    public function updatePermissionRequest(PermissionRequest $permissionRequest, array $data): PermissionRequest
    {
        return DB::transaction(function () use ($permissionRequest, $data) {
            // Preparar metadata
            $metadata = $permissionRequest->metadata ?? [];

            // Actualizar o agregar el campo skip_immediate_supervisor
            if (isset($data['skip_immediate_supervisor'])) {
                $metadata['skip_immediate_supervisor'] = (bool) $data['skip_immediate_supervisor'];
                if ($data['skip_immediate_supervisor']) {
                    $metadata['skip_reason_timestamp'] = now()->toISOString();
                }
            } elseif (!isset($data['skip_immediate_supervisor']) && isset($metadata['skip_immediate_supervisor'])) {
                // Si no viene en el request pero existía antes, removerlo
                unset($metadata['skip_immediate_supervisor']);
                unset($metadata['skip_reason_timestamp']);
            }

            $permissionRequest->update([
                'permission_type_id' => $data['permission_type_id'],
                'reason' => $data['reason'],
                'metadata' => $metadata,
            ]);

            return $permissionRequest;
        });
    }

    /**
     * Enviar solicitud para aprobación
     */
    public function submitForApproval(PermissionRequest $permissionRequest, bool $skipSignatureValidation = false): bool
    {
        if (!$skipSignatureValidation && !$permissionRequest->canBeSubmitted()) {
            return false;
        }

        return DB::transaction(function () use ($permissionRequest) {
            $permissionRequest->status = PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS;
            $permissionRequest->submitted_at = now();
            $permissionRequest->current_approval_level = 1;

            // Verificar si se debe saltar al jefe inmediato
            $skipImmediateSupervisor = $permissionRequest->metadata['skip_immediate_supervisor'] ?? false;

            if ($skipImmediateSupervisor) {
                // Caso especial: enviar directo a RRHH
                $hrChief = $this->getHRChief();

                if ($hrChief) {
                    $permissionRequest->approvals()->create([
                        'approver_id' => $hrChief->id,
                        'approval_level' => 1,
                        'status' => 'pending',
                    ]);

                    $approver = $hrChief;
                }
            } else {
                // Flujo normal: crear registro de aprobación para el jefe inmediato
                $permissionRequest->approvals()->create([
                    'approver_id' => $permissionRequest->user->immediate_supervisor_id,
                    'approval_level' => 1,
                    'status' => 'pending',
                ]);

                $approver = User::find($permissionRequest->user->immediate_supervisor_id);
            }

            $saved = $permissionRequest->save();

            if ($saved && isset($approver)) {
                // Disparar evento de solicitud enviada
                if ($skipImmediateSupervisor) {
                    // Notificar a TODOS los jefes de RRHH
                    $allHRChiefs = User::whereHas('role', function ($query) {
                        $query->where('name', 'jefe_rrhh');
                    })->get();

                    foreach ($allHRChiefs as $hr) {
                        event(new PermissionRequestSubmitted($permissionRequest, $hr));
                    }
                } else {
                    // Notificar solo al jefe inmediato
                    event(new PermissionRequestSubmitted($permissionRequest, $approver));
                }
            }

            return $saved;
        });
    }

    /**
     * Aprobar una solicitud
     */
    public function approve(PermissionRequest $permissionRequest, User $approver, ?string $comments = null): bool
    {
        $approval = $permissionRequest->currentApproval();

        if (!$approval) {
            return false;
        }

        // Validar que el aprobador tenga permisos
        // Para nivel 1: jefe inmediato O RRHH si es caso especial
        // Para nivel 2: cualquier usuario con rol jefe_rrhh puede aprobar
        if ($permissionRequest->current_approval_level === 1) {
            $skipImmediateSupervisor = $permissionRequest->metadata['skip_immediate_supervisor'] ?? false;

            if ($skipImmediateSupervisor) {
                // Caso especial: cualquier jefe de RRHH puede aprobar nivel 1
                if (!$approver->hasRole('jefe_rrhh')) {
                    return false;
                }
                // Actualizar el approver_id al RRHH que está aprobando
                $approval->approver_id = $approver->id;
            } else {
                // Caso normal: debe ser el jefe inmediato asignado
                if ($approval->approver_id !== $approver->id) {
                    return false;
                }
            }
        } elseif ($permissionRequest->current_approval_level === 2) {
            // Nivel 2: cualquier jefe de RRHH puede aprobar
            if (!$approver->hasRole('jefe_rrhh')) {
                return false;
            }
            // Actualizar el approver_id al usuario que realmente está aprobando
            $approval->approver_id = $approver->id;
        } else {
            // Nivel desconocido
            return false;
        }

        return DB::transaction(function () use ($permissionRequest, $approval, $comments, $approver) {
            // Verificar si es caso especial (jefe inmediato no disponible)
            $skipImmediateSupervisor = $permissionRequest->metadata['skip_immediate_supervisor'] ?? false;
            $isSpecialCase = $skipImmediateSupervisor && $permissionRequest->current_approval_level === 1;

            // Determinar el método de aprobación
            $hasDigitalSignature = false;
            if ($permissionRequest->current_approval_level === 1) {
                $hasDigitalSignature = $permissionRequest->hasLevel1FirmaPeruSignature();
            } elseif ($permissionRequest->current_approval_level === 2) {
                $hasDigitalSignature = $permissionRequest->hasLevel2FirmaPeruSignature();
            }

            $approvalMethod = $hasDigitalSignature ? 'digital_signature' : 'manual_approval';

            // Actualizar la aprobación actual (nivel 1)
            $approval->update([
                'status' => 'approved',
                'comments' => $comments,
                'approved_at' => now(),
                'metadata' => array_merge($approval->metadata ?? [], [
                    'approval_method' => $approvalMethod,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
            ]);

            $nextApprover = null;

            if ($isSpecialCase) {
                // CASO ESPECIAL: Aprobación completa con una sola firma
                // Crear y aprobar automáticamente nivel 2 con el mismo aprobador
                $permissionRequest->approvals()->create([
                    'approver_id' => $approver->id,
                    'approval_level' => 2,
                    'status' => 'approved',
                    'comments' => $comments,
                    'approved_at' => now(),
                    'metadata' => [
                        'approval_method' => $approvalMethod,
                        'ip_address' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                        'special_case' => true,
                        'reason' => 'Jefe inmediato no disponible - Aprobación completa por RRHH'
                    ]
                ]);

                // Marcar como aprobado final
                $permissionRequest->status = PermissionRequest::STATUS_APPROVED;
                $permissionRequest->current_approval_level = 2;

                // Crear registro de seguimiento
                if (!$permissionRequest->tracking) {
                    $permissionRequest->tracking()->create([
                        'employee_dni' => $permissionRequest->user->dni,
                        'tracking_status' => \App\Models\PermissionTracking::STATUS_PENDING,
                    ]);
                }

                // No hay siguiente aprobador porque se aprobó completamente
                $nextApprover = null;

            } else {
                // FLUJO NORMAL: Verificar si necesita aprobación de RRHH
                if ($this->needsHRApproval($permissionRequest)) {
                    $permissionRequest->status = PermissionRequest::STATUS_PENDING_HR;
                    $permissionRequest->current_approval_level = 2;

                    // Crear aprobación para RRHH
                    $hrChief = $this->getHRChief();
                    if ($hrChief) {
                        $permissionRequest->approvals()->create([
                            'approver_id' => $hrChief->id,
                            'approval_level' => 2,
                            'status' => 'pending',
                        ]);
                        $nextApprover = $hrChief;
                    }
                } else {
                    // Aprobación final
                    $permissionRequest->status = PermissionRequest::STATUS_APPROVED;
                    // Crear registro de seguimiento
                    if (!$permissionRequest->tracking) {
                        $permissionRequest->tracking()->create([
                            'employee_dni' => $permissionRequest->user->dni,
                            'tracking_status' => \App\Models\PermissionTracking::STATUS_PENDING,
                        ]);
                    }
                }
            }

            $saved = $permissionRequest->save();

            if ($saved) {
                // Disparar evento de aprobación
                event(new PermissionRequestApproved($permissionRequest, $approver, $nextApprover));
            }

            return $saved;
        });
    }

    /**
     * Rechazar una solicitud
     */
    public function reject(PermissionRequest $permissionRequest, User $approver, string $comments): bool
    {
        $approval = $permissionRequest->currentApproval();

        if (!$approval) {
            return false;
        }

        // Validar que el aprobador tenga permisos
        // Para nivel 1: jefe inmediato O RRHH si es caso especial
        // Para nivel 2: cualquier usuario con rol jefe_rrhh puede rechazar
        if ($permissionRequest->current_approval_level === 1) {
            $skipImmediateSupervisor = $permissionRequest->metadata['skip_immediate_supervisor'] ?? false;

            if ($skipImmediateSupervisor) {
                // Caso especial: cualquier jefe de RRHH puede rechazar nivel 1
                if (!$approver->hasRole('jefe_rrhh')) {
                    return false;
                }
                // Actualizar el approver_id al RRHH que está rechazando
                $approval->approver_id = $approver->id;
            } else {
                // Caso normal: debe ser el jefe inmediato asignado
                if ($approval->approver_id !== $approver->id) {
                    return false;
                }
            }
        } elseif ($permissionRequest->current_approval_level === 2) {
            // Nivel 2: cualquier jefe de RRHH puede rechazar
            if (!$approver->hasRole('jefe_rrhh')) {
                return false;
            }
            // Actualizar el approver_id al usuario que realmente está rechazando
            $approval->approver_id = $approver->id;
        } else {
            // Nivel desconocido
            return false;
        }

        return DB::transaction(function () use ($permissionRequest, $approval, $comments, $approver) {
            $approval->update([
                'status' => 'rejected',
                'comments' => $comments,
                'approved_at' => now(),
                'metadata' => array_merge($approval->metadata ?? [], [
                    'approval_method' => 'manual_rejection',
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ])
            ]);

            $permissionRequest->status = PermissionRequest::STATUS_REJECTED;
            
            $saved = $permissionRequest->save();
            
            if ($saved) {
                // Disparar evento de rechazo
                event(new PermissionRequestRejected($permissionRequest, $approver, $comments));
            }
            
            return $saved;
        });
    }

    /**
     * Cancelar una solicitud
     */
    public function cancel(PermissionRequest $permissionRequest): bool
    {
        if (!in_array($permissionRequest->status, [
            PermissionRequest::STATUS_DRAFT,
            PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS
        ])) {
            return false;
        }

        $permissionRequest->status = PermissionRequest::STATUS_CANCELLED;
        return $permissionRequest->save();
    }

    /**
     * Subir documentos a una solicitud
     */
    public function uploadDocuments(PermissionRequest $permissionRequest, array $files, array $documentTypes = []): array
    {
        $uploadedDocuments = [];

        foreach ($files as $index => $file) {
            if ($file instanceof UploadedFile && $file->isValid()) {
                $document = $this->storeDocument($permissionRequest, $file, $documentTypes[$index] ?? 'otros');
                $uploadedDocuments[] = $document;
            }
        }

        return $uploadedDocuments;
    }

    /**
     * Almacenar un documento individual
     */
    private function storeDocument(PermissionRequest $permissionRequest, UploadedFile $file, string $documentType): Document
    {
        $path = $file->store('permission-documents/' . date('Y/m'), 'public');
        
        return Document::create([
            'permission_request_id' => $permissionRequest->id,
            'original_name' => $file->getClientOriginalName(),
            'stored_name' => basename($path),
            'file_path' => $path,
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => $documentType,
            'file_hash' => hash_file('sha256', $file->getRealPath()),
        ]);
    }

    /**
     * Eliminar un documento
     */
    public function deleteDocument(Document $document): bool
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        return $document->delete();
    }

    /**
     * Verificar si una solicitud necesita aprobación de RRHH
     */
    private function needsHRApproval(PermissionRequest $permissionRequest): bool
    {
        // Todos los permisos necesitan aprobación de RRHH según la normativa
        return $permissionRequest->current_approval_level === 1;
    }

    /**
     * Obtener el jefe de RRHH
     */
    private function getHRChief(): ?User
    {
        return User::whereHas('role', function ($query) {
            $query->where('name', 'jefe_rrhh');
        })->first();
    }

    /**
     * Obtener estadísticas de permisos de un usuario
     */
    public function getUserPermissionStats(User $user, int $year = null, int $month = null): array
    {
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        $stats = [];
        $permissionTypes = PermissionType::active()->get();

        foreach ($permissionTypes as $type) {
            $requests = $user->permissionRequests()
                ->where('permission_type_id', $type->id)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->get();

            $stats[$type->code] = [
                'type_name' => $type->name,
                'total_requests' => $requests->count(),
                'max_times_per_month' => $type->max_times_per_month,
                'remaining_requests' => $type->max_times_per_month ? 
                    max(0, $type->max_times_per_month - $requests->count()) : null,
            ];
        }

        return $stats;
    }

    /**
     * Validar si un usuario puede solicitar un permiso específico
     */
    public function canUserRequestPermission(User $user, PermissionType $permissionType, Carbon $startDate): array
    {
        $errors = [];

        // Verificar límite de frecuencia mensual
        if ($permissionType->hasFrequencyLimit()) {
            $monthlyCount = $user->permissionRequests()
                ->where('permission_type_id', $permissionType->id)
                ->whereYear('created_at', $startDate->year)
                ->whereMonth('created_at', $startDate->month)
                ->whereNotIn('status', ['rejected', 'cancelled'])
                ->count();

            if ($monthlyCount >= $permissionType->max_times_per_month) {
                $errors[] = "Ya alcanzó el límite de {$permissionType->max_times_per_month} permisos por mes.";
            }
        }

        return $errors;
    }
}