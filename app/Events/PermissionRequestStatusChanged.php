<?php

namespace App\Events;

use App\Models\PermissionRequest;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionRequestStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PermissionRequest $permissionRequest,
        public string $previousStatus,
        public string $newStatus
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('approvals'),
            new Channel('permissions.user.' . $this->permissionRequest->user_id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'permission.status.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'permission' => [
                'id' => $this->permissionRequest->id,
                'request_number' => $this->permissionRequest->request_number,
                'status' => $this->newStatus,
                'previous_status' => $this->previousStatus,
                'current_approval_level' => $this->permissionRequest->current_approval_level,
                'user' => [
                    'name' => $this->permissionRequest->user->name,
                    'email' => $this->permissionRequest->user->email,
                ],
                'permission_type' => $this->permissionRequest->permissionType->name,
            ],
            'status_label' => $this->permissionRequest->getStatusLabel(),
            'status_color' => $this->permissionRequest->getStatusColor(),
        ];
    }
}