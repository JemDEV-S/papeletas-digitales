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
            // Crear la solicitud
            $permissionRequest = $user->permissionRequests()->create([
                'permission_type_id' => $data['permission_type_id'],
                'reason' => $data['reason'],
                'status' => PermissionRequest::STATUS_DRAFT,
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
            $permissionRequest->update([
                'permission_type_id' => $data['permission_type_id'],
                'reason' => $data['reason'],
            ]);

            return $permissionRequest;
        });
    }

    /**
     * Enviar solicitud para aprobación
     */
    public function submitForApproval(PermissionRequest $permissionRequest): bool
    {
        if (!$permissionRequest->canBeSubmitted()) {
            return false;
        }

        return DB::transaction(function () use ($permissionRequest) {
            
            $permissionRequest->status = PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS;
            $permissionRequest->submitted_at = now();
            $permissionRequest->current_approval_level = 1;

            // Crear registro de aprobación para el jefe inmediato
            $permissionRequest->approvals()->create([
                'approver_id' => $permissionRequest->user->immediate_supervisor_id,
                'approval_level' => 1,
                'status' => 'pending',
            ]);

            $saved = $permissionRequest->save();
            
            if ($saved) {
                // Disparar evento de solicitud enviada
                $approver = User::find($permissionRequest->user->immediate_supervisor_id);
                if ($approver) {
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
        // Para nivel 1 (jefe inmediato): debe ser el supervisor asignado específicamente
        // Para nivel 2 (RRHH): cualquier usuario con rol jefe_rrhh puede aprobar
        if ($permissionRequest->current_approval_level === 1) {
            // Nivel 1: validación estricta - debe ser el jefe inmediato asignado
            if ($approval->approver_id !== $approver->id) {
                return false;
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
            // Actualizar la aprobación actual
            $approval->update([
                'status' => 'approved',
                'comments' => $comments,
                'approved_at' => now(),
            ]);

            $nextApprover = null;
            
            // Verificar si necesita aprobación de RRHH
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
        // Para nivel 1 (jefe inmediato): debe ser el supervisor asignado específicamente
        // Para nivel 2 (RRHH): cualquier usuario con rol jefe_rrhh puede rechazar
        if ($permissionRequest->current_approval_level === 1) {
            // Nivel 1: validación estricta - debe ser el jefe inmediato asignado
            if ($approval->approver_id !== $approver->id) {
                return false;
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