<?php

use App\Models\Setting;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar el auto-registro de llegadas a la hora de cierre configurable, todos los días laborables
Schedule::command('tracking:auto-register-returns')
    ->everyMinute()
    ->weekdays()
    ->when(function () {
        $closingTime = Setting::get('auto_return_time', '17:00');

        return now('America/Lima')->format('H:i') === $closingTime;
    })
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Auto-register returns command executed successfully at closing time');
    })
    ->onFailure(function () {
        \Log::error('Auto-register returns command failed at closing time');
    });
