<?php

require_once 'bootstrap/app.php';

use App\Models\User;
use App\Models\PermissionTracking;

$user = User::where('dni', '73980928')->first();

if ($user) {
    echo "Usuario encontrado: " . $user->first_name . " " . $user->last_name . "\n";
    echo "DNI: " . $user->dni . "\n";
    echo "Activo: " . ($user->is_active ? "Sí" : "No") . "\n\n";
    
    $trackings = PermissionTracking::where('employee_dni', '73980928')
        ->orderBy('created_at', 'desc')
        ->take(10)
        ->get();
    
    echo "Trackings encontrados: " . $trackings->count() . "\n\n";
    
    foreach ($trackings as $t) {
        echo "- ID: " . $t->id . "\n";
        echo "  Status: " . $t->tracking_status . "\n";
        echo "  Created: " . $t->created_at . "\n";
        echo "  Departure: " . ($t->departure_datetime ?? 'NULL') . "\n";
        echo "  Return: " . ($t->return_datetime ?? 'NULL') . "\n";
        echo "  Permission Request ID: " . $t->permission_request_id . "\n";
        echo "  ---\n";
    }
    
    // Revisar trackings activos específicamente
    $activeTrackings = PermissionTracking::where('employee_dni', '73980928')
        ->whereIn('tracking_status', [
            PermissionTracking::STATUS_PENDING,
            PermissionTracking::STATUS_OUT
        ])
        ->get();
    
    echo "\nTrackings activos (pending/out): " . $activeTrackings->count() . "\n";
    foreach ($activeTrackings as $t) {
        echo "- ID: " . $t->id . ", Status: " . $t->tracking_status . ", Created: " . $t->created_at . "\n";
    }
    
} else {
    echo "Usuario no encontrado con DNI 73980928\n";
}