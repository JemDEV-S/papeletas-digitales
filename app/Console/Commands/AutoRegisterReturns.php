<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PermissionTracking;
use App\Models\PermissionRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoRegisterReturns extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tracking:auto-register-returns {--dry-run : Execute in dry-run mode without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically register return for employees who are still out at 5:00 PM';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        $this->info('=== Auto-Register Returns Command ===');
        $this->info('Execution time: ' . now()->format('Y-m-d H:i:s'));

        if ($dryRun) {
            $this->warn('Running in DRY-RUN mode - no changes will be made');
        }

        // Obtener empleados que salieron pero aún no han regresado
        $trackingsOut = PermissionTracking::where('tracking_status', PermissionTracking::STATUS_OUT)
            ->with(['permissionRequest.user'])
            ->get();

        if ($trackingsOut->isEmpty()) {
            $this->info('No employees are currently out.');
            return Command::SUCCESS;
        }

        $this->info("Found {$trackingsOut->count()} employees currently out:");
        $this->newLine();

        $registered = 0;
        $skipped = 0;

        // Crear un usuario del sistema para registrar automáticamente
        $systemUser = User::whereHas('role', function($q) {
            $q->where('name', 'jefe_rrhh');
        })->first();

        if (!$systemUser) {
            $this->error('No admin user found to register returns. Please ensure at least one admin user exists.');
            return Command::FAILURE;
        }

        foreach ($trackingsOut as $tracking) {
            $employee = $tracking->permissionRequest->user;
            $departureTime = Carbon::parse($tracking->departure_datetime);
            $employeeName = $employee->name;
            $employeeDni = $tracking->employee_dni;

            $this->line("- {$employeeName} (DNI: {$employeeDni})");
            $this->line("  Departure: {$departureTime->format('Y-m-d H:i:s')}");

            if ($dryRun) {
                $this->line("  [DRY-RUN] Would register return at 5:00 PM");
                $registered++;
            } else {
                // Registrar el regreso automáticamente
                $returnTime = now()->setTime(17, 0, 0); // 5:00 PM

                $tracking->return_datetime = $returnTime;
                $tracking->tracking_status = PermissionTracking::STATUS_RETURNED;
                $tracking->registered_by_user_id = $systemUser->id;
                $tracking->calculateActualHours();

                $notes = "Regreso registrado automáticamente por el sistema a las 17:00 hrs (horario de cierre).";
                if ($tracking->notes) {
                    $tracking->notes = $tracking->notes . "\n" . $notes;
                } else {
                    $tracking->notes = $notes;
                }

                if ($tracking->save()) {

                    $this->info("  ✓ Return registered at {$returnTime->format('H:i:s')} (Hours used: {$tracking->actual_hours_used})");
                    $registered++;

                    // Log the action
                    Log::info("Auto-registered return for employee", [
                        'tracking_id' => $tracking->id,
                        'employee_id' => $employee->id,
                        'employee_name' => $employeeName,
                        'departure_datetime' => $departureTime,
                        'return_datetime' => $returnTime,
                        'actual_hours_used' => $tracking->actual_hours_used
                    ]);
                } else {
                    $this->error("  ✗ Failed to register return");
                    $skipped++;
                }
            }

            $this->newLine();
        }

        // Summary
        $this->newLine();
        $this->info('=== Summary ===');

        if ($dryRun) {
            $this->info("Would register returns: {$registered}");
        } else {
            $this->info("Successfully registered: {$registered}");
            if ($skipped > 0) {
                $this->warn("Failed: {$skipped}");
            }
        }

        return Command::SUCCESS;
    }
}
