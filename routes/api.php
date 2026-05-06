<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FirmaPeruController;
use App\Http\Controllers\Api\AgentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Estas rutas están cargadas por el RouteServiceProvider y están
| asignadas al grupo de middleware "api", que proporciona características
| como limitación de velocidad y autenticación por tokens.
|
*/

// Rutas específicas para FIRMA PERÚ (sin CSRF)
Route::prefix('firma-peru')->name('api.firma-peru.')->group(function () {
    // Endpoint para recibir parámetros desde firmaperu.min.js
    Route::post('/param', [FirmaPeruController::class, 'getSignatureParameters'])
        ->name('parameters');
        
    // Endpoints para descargar documentos (llamados desde FIRMA PERÚ)
    Route::get('/document/{permission}', [FirmaPeruController::class, 'getDocument'])
        ->name('document');
    Route::get('/signed-document/{permission}', [FirmaPeruController::class, 'getSignedDocument'])
        ->name('signed-document');
        
    // Endpoint para recibir documentos firmados
    Route::post('/upload/{permission}', [FirmaPeruController::class, 'uploadSignedDocument'])
        ->name('upload');
});

// Rutas para agentes ZKTeco
Route::prefix('agent')->name('api.agent.')->group(function () {
    // Rutas públicas (verificación de conexión)
    Route::get('/ping', [AgentController::class, 'ping'])->name('ping');
    
    // Rutas protegidas por autenticación (requieren token de agente)
    Route::middleware('agent.auth')->group(function () {
        // Gestión del agente
        Route::post('/register', [AgentController::class, 'register'])->name('register');
        Route::post('/heartbeat', [AgentController::class, 'heartbeat'])->name('heartbeat');
        Route::get('/status', [AgentController::class, 'getStatus'])->name('status');
        
        // Sincronización de empleados
        Route::get('/employees', [AgentController::class, 'getEmployees'])->name('employees');
        
        // Eventos de acceso
        Route::post('/access-events', [AgentController::class, 'storeAccessEvent'])->name('access-events.store');
        Route::get('/permission-trackings/{dni}', [AgentController::class, 'getPermissionTrackings'])->name('permission-trackings.show');
    });
});