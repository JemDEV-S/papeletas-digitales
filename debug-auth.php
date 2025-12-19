<?php
/**
 * Script de diagnóstico para verificar autenticación en cPanel
 *
 * Usar: Subir este archivo a la raíz del proyecto en cPanel y visitar:
 * https://tudominio.com/debug-auth.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<pre>";
echo "=== DIAGNÓSTICO DE AUTENTICACIÓN ===\n\n";

echo "PHP Version: " . PHP_VERSION . "\n";
echo "Laravel Version: " . app()->version() . "\n\n";

// Verificar sesión
echo "Session ID: " . session()->getId() . "\n";
echo "Session Driver: " . config('session.driver') . "\n\n";

// Verificar autenticación
$user = auth()->user();
echo "Usuario autenticado: " . (auth()->check() ? 'SÍ' : 'NO') . "\n";

if ($user) {
    echo "User ID: " . $user->id . "\n";
    echo "User ID type: " . gettype($user->id) . "\n";
    echo "User name: " . $user->name . "\n";
    echo "User email: " . $user->email . "\n";

    // Verificar rol
    $user->load('role');
    echo "User role: " . ($user->role ? $user->role->name : 'sin rol') . "\n\n";

    // Probar con una solicitud
    $permission = \App\Models\PermissionRequest::where('user_id', $user->id)->first();
    if ($permission) {
        echo "=== PRUEBA CON SOLICITUD #{$permission->id} ===\n";
        echo "Permission user_id: " . $permission->user_id . "\n";
        echo "Permission user_id type: " . gettype($permission->user_id) . "\n";
        echo "IDs match (===): " . ($permission->user_id === $user->id ? 'SÍ' : 'NO') . "\n";
        echo "IDs match (==): " . ($permission->user_id == $user->id ? 'SÍ' : 'NO') . "\n";

        // Probar isOwner
        $isOwner = $user && $permission->user_id === $user->id;
        echo "\$isOwner variable: " . ($isOwner ? 'TRUE' : 'FALSE') . "\n";

        // Probar canApprove
        $canApprove = $user && $user->canApprove($permission);
        echo "\$canApprove variable: " . ($canApprove ? 'TRUE' : 'FALSE') . "\n";
    } else {
        echo "\nNo se encontraron solicitudes del usuario.\n";
    }
} else {
    echo "\n⚠️ NO HAY USUARIO AUTENTICADO\n";
    echo "Por favor, inicia sesión primero en el sistema.\n";
}

echo "\n=== FIN DEL DIAGNÓSTICO ===\n";
echo "</pre>";

// IMPORTANTE: Eliminar este archivo después de usarlo
echo "<p style='color: red; font-weight: bold;'>⚠️ IMPORTANTE: Elimina este archivo después de usarlo por seguridad.</p>";
