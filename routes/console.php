<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Programar el auto-registro de llegadas a las 5:00 PM todos los días laborables
Schedule::command('tracking:auto-register-returns')
    ->dailyAt('17:00')
    ->weekdays()
    ->withoutOverlapping()
    ->runInBackground()
    ->onSuccess(function () {
        \Log::info('Auto-register returns command executed successfully at 5:00 PM');
    })
    ->onFailure(function () {
        \Log::error('Auto-register returns command failed at 5:00 PM');
    });
