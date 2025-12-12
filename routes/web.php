<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PermissionRequestController;
use App\Http\Controllers\PermissionTrackingController;
use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\SignatureController; // Nuevo controlador para firma digital
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\AgentManagementController;
use App\Http\Controllers\HRReportsController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DepartmentController as AdminDepartmentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Rutas que requieren autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');
    Route::get('/dashboard/my-requests', [DashboardController::class, 'getMyRequestsSection'])->name('dashboard.my-requests');
    Route::get('/dashboard/team-requests', [DashboardController::class, 'getTeamRequestsSection'])->name('dashboard.team-requests');
   
    // Perfil de usuario
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
   
    // Solicitudes de permisos - Todos los usuarios autenticados pueden acceder
    Route::resource('permissions', PermissionRequestController::class);
    Route::get('/permissions/{permission}/pdf', [PermissionRequestController::class, 'generatePdf'])->name('permissions.pdf');
    Route::get('/permissions/{permission}/tracking-pdf', [PermissionRequestController::class, 'getTrackingPdf'])->name('permissions.tracking-pdf');
    Route::post('/permissions/{permission}/submit', [PermissionRequestController::class, 'submit'])->name('permissions.submit');
    Route::post('/permissions/{permission}/submit-without-signature', [PermissionRequestController::class, 'submitWithoutSignature'])->name('permissions.submit-without-signature');
    Route::post('/permissions/{permission}/cancel', [PermissionRequestController::class, 'cancel'])->name('permissions.cancel');
    Route::post('/permissions/{permission}/documents', [PermissionRequestController::class, 'uploadDocument'])->name('permissions.documents.upload');
    Route::get('/permissions/{permission}/documents/{document}', [PermissionRequestController::class, 'viewDocument'])->name('permissions.documents.view');
    Route::delete('/permissions/{permission}/documents/{document}', [PermissionRequestController::class, 'deleteDocument'])->name('permissions.documents.delete');
    
    // === NUEVAS RUTAS PARA FIRMA DIGITAL ===
    Route::prefix('permissions/{permission}')->name('permissions.')->group(function () {
        
        // Proceso de firma digital
        Route::get('/signature-process', [SignatureController::class, 'showSignatureProcess'])
            ->name('signature-process'); // Solo el dueño puede firmar
        
        // Descargar PDF sin firmar
        Route::get('/download-unsigned-pdf', [SignatureController::class, 'downloadUnsignedPdf'])
            ->name('download-unsigned-pdf');
        
        // Subir PDF firmado
        Route::post('/upload-signed-pdf', [SignatureController::class, 'uploadSignedPdf'])
            ->name('upload-signed-pdf');
        
        // Enviar solicitud firmada para aprobación
        Route::post('/submit-signed', [SignatureController::class, 'submitSignedRequest'])
            ->name('submit-signed');
        
        // Descargar PDF firmado
        Route::get('/download-signed-pdf', [SignatureController::class, 'downloadSignedPdf'])
            ->name('download-signed-pdf'); // Empleado, jefes y admin pueden descargar
        
        // Remover firma digital
        Route::post('/remove-signature', [SignatureController::class, 'removeSignature'])
            ->name('remove-signature');
        
        // Verificar firma digital (API para AJAX)
        Route::get('/verify-signature', [SignatureController::class, 'verifySignature'])
            ->name('verify-signature')
            ->middleware('can:view,permission');
    });
    
    // === FIN NUEVAS RUTAS PARA FIRMA DIGITAL ===

// === RUTAS PARA SEGUIMIENTO FÍSICO DE PERMISOS ===
    Route::middleware(['auth'])->prefix('tracking')->name('tracking.')->group(function () {
        // Dashboard principal para control de seguimiento (DEBE IR ANTES de /{tracking})
        Route::get('/dashboard', [PermissionTrackingController::class, 'hrDashboard'])->name('hr-dashboard');
        
        // API endpoints (rutas específicas ANTES de las genéricas)
        Route::get('/api/active-trackings', [PermissionTrackingController::class, 'getActiveTrackings'])->name('api.active-trackings');
        Route::post('/api/scan-dni', [PermissionTrackingController::class, 'scanDni'])->name('api.scan-dni');
        Route::post('/api/register-departure', [PermissionTrackingController::class, 'registerDeparture'])->name('api.register-departure');
        Route::post('/api/register-return', [PermissionTrackingController::class, 'registerReturn'])->name('api.register-return');
        Route::post('/api/update-departure', [PermissionTrackingController::class, 'updateDeparture'])->name('api.update-departure');
        Route::post('/api/update-return', [PermissionTrackingController::class, 'updateReturn'])->name('api.update-return');
        Route::post('/api/mark-overdue', [PermissionTrackingController::class, 'markOverdue'])->name('api.mark-overdue');
        
        // Vista general de seguimientos (todos los usuarios pueden ver)
        Route::get('/', [PermissionTrackingController::class, 'index'])->name('index');
        
        // IMPORTANTE: Esta ruta genérica DEBE IR AL FINAL y solo acepta números
        Route::get('/{tracking}', [PermissionTrackingController::class, 'show'])
            ->name('show')
            ->where('tracking', '[0-9]+'); // Solo acepta números
    });
    // === FIN RUTAS PARA SEGUIMIENTO FÍSICO ===
   
    // API Routes for notifications and real-time updates
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/notifications/check', [NotificationController::class, 'checkForUpdates'])->name('notifications.check');
        Route::get('/notifications/count', [NotificationController::class, 'getUnreadCount'])->name('notifications.count');
        Route::get('/notifications/all', [NotificationController::class, 'getAllNotifications'])->name('notifications.all');
        Route::get('/notifications/stats', [NotificationController::class, 'getNotificationStats'])->name('notifications.stats');
        Route::post('/notifications/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::delete('/notifications/{id}', [NotificationController::class, 'deleteNotification'])->name('notifications.delete');
        Route::delete('/notifications', [NotificationController::class, 'clearAllNotifications'])->name('notifications.clear');
        Route::get('/approvals/stats', [NotificationController::class, 'getApprovalStats'])->name('approvals.stats');
        Route::get('/permissions/list', [NotificationController::class, 'getPermissionsList'])->name('permissions.list');
        Route::get('/approvals/list', [NotificationController::class, 'getApprovalsList'])->name('approvals.list');
        
        // === FIRMA PERÚ API ENDPOINTS ===
        // Nota: Las rutas principales de FIRMA PERÚ están en routes/api.php para evitar CSRF
            
        // Endpoints para iniciar procesos de firma
        Route::post('/firma-peru/initiate-employee/{permission}', [\App\Http\Controllers\Api\FirmaPeruController::class, 'initiateEmployeeSignature'])
            ->name('firma-peru.initiate-employee');
        Route::post('/firma-peru/initiate-level1/{permission}', [\App\Http\Controllers\Api\FirmaPeruController::class, 'initiateLevel1Signature'])
            ->name('firma-peru.initiate-level1');
        Route::post('/firma-peru/initiate-level2/{permission}', [\App\Http\Controllers\Api\FirmaPeruController::class, 'initiateLevel2Signature'])
            ->name('firma-peru.initiate-level2');
            
        // Endpoints para consultar estado de firmas
        Route::get('/firma-peru/signature-status/{permission}', [\App\Http\Controllers\Api\FirmaPeruController::class, 'getSignatureStatus'])
            ->name('firma-peru.signature-status');
        Route::get('/firma-peru/verify-signatures/{permission}', [\App\Http\Controllers\Api\FirmaPeruController::class, 'verifyAllSignatures'])
            ->name('firma-peru.verify-signatures');
        
        // Rutas adicionales para notificaciones
        Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-all-read');
        Route::get('/notifications/pending-approvals', [NotificationController::class, 'pendingApprovalsCount'])->name('notifications.pending-approvals');
        
        // FIRMA PERÚ test endpoint
        Route::get('/firma-peru/test-token', function() {
            $firmaPeruService = app(\App\Services\FirmaPeruService::class);
            $result = $firmaPeruService->generateToken();
            
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'] ?? 'Token generado exitosamente',
                'has_token' => isset($result['token']),
                'debug_info' => $result['debug_info'] ?? null
            ]);
        })->name('firma-peru.test-token');

        // Endpoint simple de test
        Route::get('/notifications/test', function() {
            $user = auth()->user();
            $notifications = \App\Models\Notification::where('user_id', $user->id)
                ->latest()
                ->take(3)
                ->get(['id', 'title', 'message', 'type', 'created_at', 'read_at']);
                
            return response()->json([
                'success' => true,
                'message' => 'API funcionando',
                'user_id' => $user->id,
                'user_name' => $user->name,
                'notifications_count' => \App\Models\Notification::where('user_id', $user->id)->count(),
                'unread_count' => \App\Models\Notification::where('user_id', $user->id)->whereNull('read_at')->count(),
                'recent_notifications' => $notifications,
                'timestamp' => now()->toISOString()
            ]);
        })->name('notifications.test');
    });

    // Aprobaciones - Solo jefes inmediatos, RRHH y admin
    Route::middleware(['role:jefe_inmediato,jefe_rrhh,admin'])->group(function () {
        Route::get('/approvals', [ApprovalController::class, 'index'])->name('approvals.index');
        Route::get('/approvals/{permission}', [ApprovalController::class, 'show'])->name('approvals.show');
        Route::post('/approvals/{permission}/approve', [ApprovalController::class, 'approve'])->name('approvals.approve');
        Route::post('/approvals/{permission}/reject', [ApprovalController::class, 'reject'])->name('approvals.reject');
        Route::post('/approvals/bulk-action', [ApprovalController::class, 'bulkAction'])->name('approvals.bulk');
    });
   
    // Reportes - Solo para RRHH y Admin
    Route::middleware(['role:jefe_rrhh,admin'])->group(function () {
        Route::get('/reports', [ApprovalController::class, 'reports'])->name('reports.index');
        
        // === REPORTES DE FIRMA DIGITAL ===
        Route::get('/reports/digital-signatures', [SignatureController::class, 'signaturesReport'])
            ->name('reports.digital-signatures');
        Route::get('/reports/signature-integrity', [SignatureController::class, 'integrityReport'])
            ->name('reports.signature-integrity');
    });

    // === REPORTES AVANZADOS PARA JEFE DE RRHH ===
    Route::middleware(['role:jefe_rrhh,admin'])->prefix('hr')->name('hr.')->group(function () {

        // Dashboard principal de reportes
        Route::get('/reports', [HRReportsController::class, 'dashboard'])->name('reports.dashboard');

        // Reportes específicos
        Route::get('/reports/requests-by-status', [HRReportsController::class, 'requestsByStatus'])->name('reports.requests-by-status');
        Route::get('/reports/requests-by-type', [HRReportsController::class, 'requestsByType'])->name('reports.requests-by-type');
        Route::get('/reports/requests-by-department', [HRReportsController::class, 'requestsByDepartment'])->name('reports.requests-by-department');
        Route::get('/reports/approval-times', [HRReportsController::class, 'approvalTimes'])->name('reports.approval-times');
        Route::get('/reports/absenteeism', [HRReportsController::class, 'absenteeism'])->name('reports.absenteeism');
        Route::get('/reports/active-employees', [HRReportsController::class, 'activeEmployees'])->name('reports.active-employees');
        Route::get('/reports/supervisor-performance', [HRReportsController::class, 'supervisorPerformance'])->name('reports.supervisor-performance');
        Route::get('/reports/real-time-tracking', [HRReportsController::class, 'realTimeTracking'])->name('reports.real-time-tracking');
        Route::get('/reports/temporal-trends', [HRReportsController::class, 'temporalTrends'])->name('reports.temporal-trends');
        Route::get('/reports/compliance', [HRReportsController::class, 'compliance'])->name('reports.compliance');

        // Reporte completo con seguimiento
        Route::get('/reports/complete-report', [HRReportsController::class, 'completeReport'])->name('reports.complete-report');

        // Exportación a Excel
        Route::get('/reports/export', [HRReportsController::class, 'export'])->name('reports.export');
    });
    
    // === GESTIÓN DE AGENTES ZKTECO (Solo RRHH) ===
    Route::middleware(['role:jefe_rrhh'])->prefix('agents')->name('agents.')->group(function () {
        // Vista principal de agentes
        Route::get('/', [AgentManagementController::class, 'index'])->name('index');
        Route::get('/{agentId}', [AgentManagementController::class, 'show'])->name('show');
        
        // Gestión de tokens
        Route::post('/tokens', [AgentManagementController::class, 'createToken'])->name('tokens.create');
        Route::delete('/tokens/{tokenId}', [AgentManagementController::class, 'revokeToken'])->name('tokens.revoke');
        
        // API para la interfaz
        Route::get('/api/data', [AgentManagementController::class, 'getAgentsData'])->name('api.data');
        Route::get('/{agentId}/logs', [AgentManagementController::class, 'getAgentLogs'])->name('api.logs');
        Route::get('/{agentId}/refresh', [AgentManagementController::class, 'refreshAgentStatus'])->name('api.refresh');
        Route::post('/{agentId}/command', [AgentManagementController::class, 'sendCommand'])->name('api.command');
    });

    // === RUTAS ADMINISTRATIVAS PARA FIRMA DIGITAL (Solo Admin) ===
    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        
        // Gestión de firmas digitales
        Route::get('/signatures', [SignatureController::class, 'adminIndex'])->name('signatures.index');
        Route::post('/signatures/{signature}/invalidate', [SignatureController::class, 'invalidateSignature'])->name('signatures.invalidate');
        Route::get('/signatures/cleanup-old-files', [SignatureController::class, 'cleanupOldFiles'])->name('signatures.cleanup');
        
        // Verificación masiva
        Route::post('/signatures/verify-all', [SignatureController::class, 'verifyAllSignatures'])->name('signatures.verify-all');
        
        // Estadísticas de uso
        Route::get('/signatures/statistics', [SignatureController::class, 'signaturesStatistics'])->name('signatures.statistics');
    });

    // === RUTAS DE ADMINISTRACIÓN DE USUARIOS Y DEPARTAMENTOS ===
    Route::middleware([App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->name('admin.')->group(function () {
        
        // Administración de Usuarios
        Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}', [AdminUserController::class, 'show'])->name('users.show');
        Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
        Route::patch('/users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
        Route::patch('/users/{user}/reset-password', [AdminUserController::class, 'resetPassword'])->name('users.reset-password');
        
        // Vista de jerarquía de usuarios
        Route::get('/users-hierarchy', [AdminUserController::class, 'hierarchy'])->name('users.hierarchy');
        Route::get('/users-hierarchy/data', [AdminUserController::class, 'getHierarchyData'])->name('users.hierarchy.data');
        
        // Administración de Departamentos
        Route::get('/departments', [AdminDepartmentController::class, 'index'])->name('departments.index');
        Route::get('/departments/create', [AdminDepartmentController::class, 'create'])->name('departments.create');
        Route::post('/departments', [AdminDepartmentController::class, 'store'])->name('departments.store');
        Route::get('/departments/{department}', [AdminDepartmentController::class, 'show'])->name('departments.show');
        Route::get('/departments/{department}/edit', [AdminDepartmentController::class, 'edit'])->name('departments.edit');
        Route::put('/departments/{department}', [AdminDepartmentController::class, 'update'])->name('departments.update');
        Route::delete('/departments/{department}', [AdminDepartmentController::class, 'destroy'])->name('departments.destroy');
        Route::patch('/departments/{department}/activate', [AdminDepartmentController::class, 'activate'])->name('departments.activate');
        
        // Vista de jerarquía de departamentos
        Route::get('/departments-hierarchy', [AdminDepartmentController::class, 'hierarchy'])->name('departments.hierarchy');
        Route::get('/departments-hierarchy/data', [AdminDepartmentController::class, 'getHierarchyData'])->name('departments.hierarchy.data');
        Route::get('/departments/{department}/users', [AdminDepartmentController::class, 'getUsersByDepartment'])->name('departments.users');
    });
});

require __DIR__.'/auth.php';