<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class SendEmailNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120]; // 30 segundos, 1 minuto, 2 minutos

    public string $email;
    public string $subject;
    public string $template;
    public array $data;
    
    // NO definir $queue como propiedad pública aquí
    
    public function __construct(
        string $email,
        string $subject,
        string $template,
        array $data,
        string $queueName = 'notifications' // Cambié el nombre del parámetro
    ) {
        $this->email = $email;
        $this->subject = $subject;
        $this->template = $template;
        $this->data = $data;
        
        // Especificar la cola usando el método onQueue
        $this->onQueue($queueName);
    }

    public function handle(): void
    {
        try {
            Mail::send($this->template, $this->data, function ($message) {
                $message->to($this->email)
                        ->subject($this->subject)
                        ->from(config('mail.from.address'), config('mail.from.name'));
            });

            Log::info('Email notification sent successfully', [
                'email' => $this->email,
                'template' => $this->template,
                'subject' => $this->subject,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send email notification', [
                'email' => $this->email,
                'template' => $this->template,
                'error' => $e->getMessage(),
                'attempts' => $this->attempts(),
            ]);
            
            // Re-lanzar la excepción para que el job sea reintentado
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Email notification job failed permanently', [
            'email' => $this->email,
            'template' => $this->template,
            'error' => $exception->getMessage(),
            'attempts' => $this->attempts(),
        ]);
    }
}