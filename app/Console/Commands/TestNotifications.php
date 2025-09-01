<?php

namespace App\Console\Commands;

use App\Events\PermissionRequestSubmitted;
use App\Models\User;
use App\Models\PermissionRequest;
use App\Models\Notification;
use Illuminate\Console\Command;

class TestNotifications extends Command
{
    protected $signature = 'test:notifications';
    protected $description = 'Test notification system';

    public function handle()
    {
        $this->info('🧪 Testing Notification System...');
        
        // Test 1: Check if users exist
        $userCount = User::count();
        $this->info("✅ Users in database: {$userCount}");
        
        if ($userCount === 0) {
            $this->error('❌ No users found. Run php artisan db:seed first');
            return;
        }
        
        // Test 2: Check notifications table
        $notificationCount = Notification::count();
        $this->info("✅ Notifications in database: {$notificationCount}");
        
        // Test 3: Try to manually dispatch an event
        $this->info('🔥 Testing event dispatch...');
        
        $user = User::first();
        $supervisor = User::whereHas('role', function ($q) {
            $q->where('name', 'jefe_inmediato');
        })->first();
        
        if (!$supervisor) {
            $supervisor = User::skip(1)->first();
            $this->warn('⚠️  No supervisor found, using second user');
        }
        
        if ($user && $supervisor) {
            $permission = PermissionRequest::first();
            
            if ($permission) {
                // Dispatch event manually
                event(new PermissionRequestSubmitted($permission, $supervisor));
                $this->info('✅ Event dispatched: PermissionRequestSubmitted');
                
                // Check if notification was created
                $newNotificationCount = Notification::count();
                if ($newNotificationCount > $notificationCount) {
                    $this->info("✅ Notification created! Count increased from {$notificationCount} to {$newNotificationCount}");
                } else {
                    $this->warn("⚠️  No new notification created. Check listeners.");
                }
            } else {
                $this->warn('⚠️  No permission requests found for testing');
            }
        }
        
        // Test 4: Check if queue is working
        $this->info('📋 Checking queue configuration...');
        $queueConnection = config('queue.default');
        $this->info("✅ Queue connection: {$queueConnection}");
        
        // Test 5: Show recent notifications
        $this->info('📨 Recent notifications:');
        $recent = Notification::latest()->take(3)->get();
        
        foreach ($recent as $notification) {
            $userName = $notification->user ? $notification->user->name : 'N/A';
            $this->line("  - {$notification->title} (Type: {$notification->type}, User: {$userName})");
        }
        
        $this->info('✅ Notification system test completed!');
    }
}