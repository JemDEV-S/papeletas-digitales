<?php

namespace App\Events;

use App\Models\PermissionRequest;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionRequestApproved implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PermissionRequest $permissionRequest,
        public User $approver,
        public ?User $nextApprover = null
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new Channel('approvals'),
            new Channel('permissions.user.' . $this->permissionRequest->user_id),
        ];

        // Si hay siguiente aprobador, notificar también
        if ($this->nextApprover) {
            $channels[] = new Channel('approvals.user.' . $this->nextApprover->id);
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'permission.approved';
    }

    public function broadcastWith(): array
    {
        $data = [
            'permission' => [
                'id' => $this->permissionRequest->id,
                'request_number' => $this->permissionRequest->request_number,
                'status' => $this->permissionRequest->status,
                'current_approval_level' => $this->permissionRequest->current_approval_level,
                'user' => [
                    'name' => $this->permissionRequest->user->name,
                    'email' => $this->permissionRequest->user->email,
                ],
                'permission_type' => $this->permissionRequest->permissionType->name,
            ],
            'approver' => [
                'name' => $this->approver->name,
                'level' => $this->permissionRequest->current_approval_level - 1,
            ],
            'is_final_approval' => $this->permissionRequest->status === 'approved',
        ];

        if ($this->nextApprover) {
            $data['next_approver_id'] = $this->nextApprover->id;
            $data['message'] = "Solicitud #{$this->permissionRequest->request_number} requiere su aprobación de RRHH";
        } else {
            $data['message'] = "Su solicitud #{$this->permissionRequest->request_number} ha sido aprobada completamente";
        }

        return $data;
    }
}