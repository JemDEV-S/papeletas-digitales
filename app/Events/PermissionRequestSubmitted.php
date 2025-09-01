<?php

namespace App\Events;

use App\Models\PermissionRequest;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionRequestSubmitted implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PermissionRequest $permissionRequest,
        public User $approver
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('approvals'),
            new Channel('approvals.user.' . $this->approver->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'permission.submitted';
    }

    public function broadcastWith(): array
    {
        return [
            'permission' => [
                'id' => $this->permissionRequest->id,
                'request_number' => $this->permissionRequest->request_number,
                'status' => $this->permissionRequest->status,
                'current_approval_level' => $this->permissionRequest->current_approval_level,
                'user' => [
                    'name' => $this->permissionRequest->user->name,
                    'email' => $this->permissionRequest->user->email,
                    'department' => $this->permissionRequest->user->department->name ?? null,
                ],
                'permission_type' => $this->permissionRequest->permissionType->name,
                'reason' => $this->permissionRequest->reason,
                'submitted_at' => $this->permissionRequest->submitted_at->toISOString(),
            ],
            'approver_id' => $this->approver->id,
            'message' => "Nueva solicitud de permiso #{$this->permissionRequest->request_number} requiere su aprobación",
        ];
    }
}