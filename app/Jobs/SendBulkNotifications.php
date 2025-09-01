<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;

    public function __construct(
        public array $userIds,
        public string $subject,
        public string $template,
        public array $data,
        public string $queue = 'bulk'
    ) {
        $this->onQueue($queue);
    }

    public function handle(): void
    {
        try {
            $users = User::whereIn('id', $this->userIds)->get();
            
            foreach ($users as $user) {
                // Personalizar datos para cada usuario
                $personalizedData = array_merge($this->data, [
                    'user_name' => $user->name,
                    'user_email' => $user->email,
                ]);

                // Enviar notificación individual en cola
                SendEmailNotification::dispatch(
                    $user->email,
                    $this->subject,
                    $this->template,
                    $personalizedData
                )->delay(now()->addSeconds(rand(1, 30))); // Espaciar envíos
            }

            Log::info('Bulk notifications dispatched', [
                'user_count' => count($this->userIds),
                'template' => $this->template,
            ]);
        } catch (\Exception $e) {
            Log::error('Error sending bulk notifications', [
                'user_count' => count($this->userIds),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}