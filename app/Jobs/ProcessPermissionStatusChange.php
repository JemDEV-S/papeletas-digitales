<?php

namespace App\Jobs;

use App\Events\PermissionRequestStatusChanged;
use App\Models\PermissionRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPermissionStatusChange implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public function __construct(
        public int $permissionId,
        public string $previousStatus,
        public string $newStatus,
        public string $queue = 'high'
    ) {
        $this->onQueue($queue);
    }

    public function handle(): void
    {
        try {
            $permission = PermissionRequest::with(['user', 'permissionType'])->find($this->permissionId);
            
            if (!$permission) {
                Log::warning('Permission request not found for status change', [
                    'permission_id' => $this->permissionId,
                ]);
                return;
            }

            // Disparar evento de cambio de estado
            event(new PermissionRequestStatusChanged($permission, $this->previousStatus, $this->newStatus));

            // Actualizar estadísticas en caché si es necesario
            $this->updateCachedStats($permission);

            Log::info('Permission status change processed', [
                'permission_id' => $this->permissionId,
                'previous_status' => $this->previousStatus,
                'new_status' => $this->newStatus,
            ]);
        } catch (\Exception $e) {
            Log::error('Error processing permission status change', [
                'permission_id' => $this->permissionId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function updateCachedStats($permission): void
    {
        try {
            // Limpiar cache de estadísticas relacionadas
            cache()->forget("approval_stats_user_{$permission->user->immediate_supervisor_id}");
            cache()->forget("approval_stats_hr");
            cache()->forget("permission_stats_user_{$permission->user_id}");
            
            // Si hay departamento, limpiar stats del departamento
            if ($permission->user->department_id) {
                cache()->forget("department_stats_{$permission->user->department_id}");
            }
        } catch (\Exception $e) {
            Log::warning('Failed to update cached stats', [
                'permission_id' => $this->permissionId,
                'error' => $e->getMessage(),
            ]);
        }
    }
}