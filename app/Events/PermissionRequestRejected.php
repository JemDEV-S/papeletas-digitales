<?php

namespace App\Events;

use App\Models\PermissionRequest;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionRequestRejected implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public PermissionRequest $permissionRequest,
        public User $approver,
        public string $comments
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
        return 'permission.rejected';
    }

    public function broadcastWith(): array
    {
        return [
            'permission' => [
                'id' => $this->permissionRequest->id,
                'request_number' => $this->permissionRequest->request_number,
                'status' => $this->permissionRequest->status,
                'user' => [
                    'name' => $this->permissionRequest->user->name,
                    'email' => $this->permissionRequest->user->email,
                ],
                'permission_type' => $this->permissionRequest->permissionType->name,
                'start_datetime' => $this->permissionRequest->start_datetime->toISOString(),
                'end_datetime' => $this->permissionRequest->end_datetime->toISOString(),
            ],
            'approver' => [
                'name' => $this->approver->name,
                'level' => $this->permissionRequest->getApprovalByLevel($this->permissionRequest->current_approval_level)->approval_level ?? 1,
            ],
            'rejection_reason' => $this->comments,
            'message' => "Su solicitud #{$this->permissionRequest->request_number} ha sido rechazada",
        ];
    }
}