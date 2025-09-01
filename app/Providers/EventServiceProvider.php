<?php

namespace App\Providers;

use App\Events\PermissionRequestSubmitted;
use App\Events\PermissionRequestApproved;
use App\Events\PermissionRequestRejected;
use App\Events\PermissionRequestStatusChanged;
use App\Listeners\SendApprovalNotification;
use App\Listeners\SendRejectionNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        
        // Permission Request Events
        PermissionRequestSubmitted::class => [
            SendApprovalNotification::class,
        ],
        
        PermissionRequestApproved::class => [
            SendApprovalNotification::class,
        ],
        
        PermissionRequestRejected::class => [
            SendRejectionNotification::class,
        ],
        
        // Status changes are handled automatically by the events above
        // but we can add additional listeners for PermissionRequestStatusChanged if needed
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        // Additional event registrations can be done here if needed
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}