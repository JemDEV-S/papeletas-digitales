<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePermissionRequestRequest;
use App\Http\Requests\UpdatePermissionRequestRequest;
use App\Models\PermissionRequest;
use App\Models\PermissionType;
use App\Models\Document;
use App\Services\PermissionService;
use App\Services\PermissionValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class PermissionRequestController extends Controller
{
    public function __construct(
        private PermissionService $permissionService,
        private PermissionValidationService $validationService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        
        $requests = $user->permissionRequests()
            ->with(['permissionType', 'approvals.approver'])
            ->latest()
            ->paginate(10);

        // Obtener estadísticas del mes actual
        $stats = $this->permissionService->getUserPermissionStats($user);

        return view('permissions.index', compact('requests', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissionTypes = PermissionType::active()->get();
        $user = Auth::user();
        
        // Obtener estadísticas para mostrar límites disponibles
        $stats = $this->permissionService->getUserPermissionStats($user);
        
        return view('permissions.create', compact('permissionTypes', 'stats'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequestRequest $request)
    {
        try {
            $user = Auth::user();
            $files = $request->file('documents', []);
            
            $permissionRequest = $this->permissionService->createPermissionRequest(
                $user,
                $request->validated(),
                $files
            );

            $message = 'Solicitud creada exitosamente. Puede editarla antes de enviarla.';
            
            // Si el usuario eligió "Guardar y Enviar"
            if ($request->input('submit_after_save')) {
                if ($permissionRequest->canBeSubmitted()) {
                    $this->permissionService->submitForApproval($permissionRequest);
                    $message = 'Solicitud creada y enviada para aprobación exitosamente.';
                } else {
                    $message = 'Solicitud creada, pero no puede ser enviada. Verifique los documentos requeridos.';
                }
            }

            return redirect()
                ->route('permissions.show', $permissionRequest)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al crear la solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PermissionRequest $permission)
    {
        if (!$this->canView($permission)) {
            abort(403, 'No tiene permisos para ver esta solicitud.');
        }

        $permission->load(['user', 'permissionType', 'documents', 'approvals.approver']);
        
        return view('permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PermissionRequest $permission)
    {
        $user = Auth::user();

        // Verificar que hay usuario autenticado
        if (!$user) {
            abort(403, 'Debe estar autenticado.');
        }

        if ($permission->user_id !== $user->id || !$permission->isEditable()) {
            abort(403, 'No puede editar esta solicitud.');
        }

        $permissionTypes = PermissionType::active()->get();
        $stats = $this->permissionService->getUserPermissionStats($user);

        return view('permissions.edit', compact('permission', 'permissionTypes', 'stats'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequestRequest $request, PermissionRequest $permission)
    {
        try {
            $this->permissionService->updatePermissionRequest($permission, $request->validated());

            $message = 'Solicitud actualizada exitosamente.';

            // Si el usuario eligió "Guardar y Enviar"
            if ($request->input('submit_after_save')) {
                if ($permission->canBeSubmitted()) {
                    $this->permissionService->submitForApproval($permission);
                    $message = 'Solicitud actualizada y enviada para aprobación exitosamente.';
                } else {
                    $message = 'Solicitud actualizada, pero no puede ser enviada. Verifique los documentos requeridos.';
                }
            }

            return redirect()
                ->route('permissions.show', $permission)
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return back()
                ->with('error', 'Error al actualizar la solicitud: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Submit the permission request for approval
     */
    public function submit(PermissionRequest $permission)
    {
        $user = Auth::user();

        // Verificar que hay usuario autenticado
        if (!$user || $permission->user_id !== $user->id) {
            abort(403, 'No puede enviar esta solicitud.');
        }

        if (!$permission->canBeSubmitted()) {
            return back()->with('error', 'La solicitud no puede ser enviada. Verifique que tenga todos los documentos requeridos.');
        }

        try {
            $this->permissionService->submitForApproval($permission);

            return redirect()
                ->route('permissions.show', $permission)
                ->with('success', 'Solicitud enviada para aprobación exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Submit a permission request for approval without digital signature
     */
    public function submitWithoutSignature(PermissionRequest $permission)
    {
        $user = Auth::user();

        // Verificar que hay usuario autenticado
        if (!$user || $permission->user_id !== $user->id) {
            abort(403, 'No puede enviar esta solicitud.');
        }

        if ($permission->status !== PermissionRequest::STATUS_DRAFT) {
            return back()->with('error', 'La solicitud no está en estado borrador.');
        }

        try {
            // Enviar con el flag skipSignatureValidation = true
            $this->permissionService->submitForApproval($permission, true);

            return redirect()
                ->route('permissions.show', $permission)
                ->with('success', 'Solicitud enviada para aprobación exitosamente (sin firma digital).');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al enviar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a permission request
     */
    public function cancel(PermissionRequest $permission)
    {
        $user = Auth::user();

        // Verificar que hay usuario autenticado
        if (!$user || $permission->user_id !== $user->id) {
            abort(403, 'No puede cancelar esta solicitud.');
        }

        try {
            if ($this->permissionService->cancel($permission)) {
                return redirect()
                    ->route('permissions.index')
                    ->with('success', 'Solicitud cancelada exitosamente.');
            }

            return back()->with('error', 'No se puede cancelar esta solicitud en su estado actual.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al cancelar la solicitud: ' . $e->getMessage());
        }
    }

    /**
     * Upload additional documents
     */
    public function uploadDocument(Request $request, PermissionRequest $permission)
    {
        $user = Auth::user();

        // Verificar que hay usuario autenticado
        if (!$user || $permission->user_id !== $user->id || !$permission->canUploadDocuments()) {
            abort(403, 'No puede agregar documentos a esta solicitud.');
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'document_type' => 'required|string|in:certificado_medico,citacion,acreditacion,resolucion_nombramiento,horario_ensenanza,horario_recuperacion,partida_nacimiento,declaracion_jurada,otros',
        ]);

        try {
            $this->permissionService->uploadDocuments(
                $permission,
                [$request->file('document')],
                [$request->document_type]
            );

            return back()->with('success', 'Documento agregado exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al subir el documento: ' . $e->getMessage());
        }
    }

    /**
     * Delete a document
     */
    public function deleteDocument(PermissionRequest $permission, Document $document)
    {
        $user = Auth::user();

        // Verificar que hay usuario autenticado
        if (!$user || $permission->user_id !== $user->id || !$permission->canUploadDocuments()) {
            abort(403, 'No puede eliminar documentos de esta solicitud.');
        }

        if ($document->permission_request_id !== $permission->id) {
            abort(404);
        }

        try {
            $this->permissionService->deleteDocument($document);
            return back()->with('success', 'Documento eliminado exitosamente.');
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el documento: ' . $e->getMessage());
        }
    }

    /**
     * Get permission type details (AJAX)
     */
    public function getPermissionTypeDetails(PermissionType $permissionType): JsonResponse
    {
        $user = Auth::user();
        
        // Verificar si el usuario puede solicitar este tipo de permiso
        $canRequest = $this->validationService->canUserRequestPermissionType($user, $permissionType);
        
        $data = [
            'id' => $permissionType->id,
            'name' => $permissionType->name,
            'description' => $permissionType->description,
            'max_hours_per_day' => $permissionType->max_hours_per_day,
            'max_hours_per_month' => $permissionType->max_hours_per_month,
            'max_times_per_month' => $permissionType->max_times_per_month,
            'with_pay' => $permissionType->with_pay,
            'required_documents' => $permissionType->getRequiredDocuments(),
            'can_request' => empty($canRequest),
            'restrictions' => $canRequest,
        ];

        // Obtener estadísticas del usuario para este tipo
        $stats = $this->permissionService->getUserPermissionStats($user);
        if (isset($stats[$permissionType->code])) {
            $data['user_stats'] = $stats[$permissionType->code];
        }

        return response()->json($data);
    }

    /**
     * Validate permission request (AJAX)
     */
    public function validateRequest(Request $request): JsonResponse
    {
        $request->validate([
            'permission_type_id' => 'required|exists:permission_types,id',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);

        try {
            $user = Auth::user();
            $permissionType = PermissionType::find($request->permission_type_id);
            $startDateTime = \Carbon\Carbon::parse($request->start_datetime);
            $endDateTime = \Carbon\Carbon::parse($request->end_datetime);

            $errors = $this->validationService->validatePermissionRequest(
                $user,
                $permissionType,
                $startDateTime,
                $endDateTime
            );

            return response()->json([
                'valid' => empty($errors),
                'errors' => $errors,
                'requested_hours' => $endDateTime->diffInMinutes($startDateTime) / 60,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'errors' => ['Error al validar la solicitud: ' . $e->getMessage()],
            ]);
        }
    }

    /**
     * Get user's permission statistics (AJAX)
     */
    public function getUserStats(Request $request): JsonResponse
    {
        $user = Auth::user();
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $stats = $this->permissionService->getUserPermissionStats($user, $year, $month);

        return response()->json($stats);
    }

    /**
     * Generate PDF for permission request (siempre muestra el original con firmas)
     */
    public function generatePdf(PermissionRequest $permission)
    {
        // Verificar autorización
        if (!$this->canView($permission)) {
            abort(403, 'No tiene permisos para ver esta solicitud.');
        }

        try {
            // Prioridad 1: PDF firmado (si existe) - preserva firmas digitales
            $signedDocument = $permission->getLatestSignedDocument();
            if ($signedDocument && $signedDocument->document_exists) {
                return $this->getSignedPdf($permission, $signedDocument);
            }

            // Prioridad 2: Generar el PDF original o con sellos de aprobación
            if (!app()->bound('App\Services\PdfGeneratorService')) {
                abort(503, 'El servicio de generación de PDF no está disponible.');
            }

            $pdfService = app('App\Services\PdfGeneratorService');

            // Verificar si el permiso tiene aprobaciones manuales (sin firma digital)
            $hasManualApprovals = $permission->approvals()
                ->where('status', 'approved')
                ->whereNotNull('metadata->approval_method')
                ->where('metadata->approval_method', 'manual_approval')
                ->exists();

            // Si tiene aprobaciones manuales y está aprobado, generar PDF con sellos
            $includeApprovalStamps = $hasManualApprovals && $permission->status === 'approved';

            $result = $pdfService->generatePermissionRequestPdf($permission, $includeApprovalStamps);

            if (!$result['success']) {
                abort(500, 'Error al generar el PDF: ' . ($result['message'] ?? 'Error desconocido'));
            }

            return response($result['pdf']->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $result['filename'] . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            abort(500, 'Error interno al generar el PDF: ' . $e->getMessage());
        }
    }

    /**
     * Servir la página de tracking separada
     */
    public function getTrackingPdf(PermissionRequest $permission)
    {
        // Verificar autorización
        if (!$this->canView($permission)) {
            abort(403, 'No tiene permisos para ver esta solicitud.');
        }

        try {
            $trackingPdfPath = $permission->getTrackingPdfPath();

            if (!$trackingPdfPath || !file_exists($trackingPdfPath)) {
                abort(404, 'Página de tracking no encontrada.');
            }

            $fileContent = file_get_contents($trackingPdfPath);
            $filename = 'tracking_' . $permission->request_number . '.pdf';

            return response($fileContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');

        } catch (\Exception $e) {
            abort(500, 'Error al servir página de tracking: ' . $e->getMessage());
        }
    }

    /**
     * Servir el PDF firmado más reciente
     */
    private function getSignedPdf(PermissionRequest $permission, \App\Models\DigitalSignature $signedDocument)
    {
        try {
            $fileContent = \Illuminate\Support\Facades\Storage::get($signedDocument->document_path);
            $filename = 'solicitud_firmada_' . $permission->request_number . '.pdf';

            return response($fileContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
                
        } catch (\Exception $e) {
            abort(500, 'Error al servir el PDF firmado: ' . $e->getMessage());
        }
    }

    /**
     * View/download a document
     */
    public function viewDocument(PermissionRequest $permission, Document $document)
    {
        try {
            // Verificar autorización
            if (!$this->canView($permission)) {
                abort(403, 'No tiene permisos para ver esta solicitud.');
            }

            // Verificar que el documento pertenece a la solicitud
            if ($document->permission_request_id !== $permission->id) {
                abort(404, 'Documento no encontrado.');
            }

            // Verificar que el archivo existe
            if (!Storage::disk('public')->exists($document->file_path)) {
                abort(404, 'El archivo no existe: ' . $document->file_path);
            }

            // Obtener el contenido del archivo
            $fileContent = Storage::disk('public')->get($document->file_path);
            
            // Determinar si es para descarga o visualización inline
            $disposition = ($document->isPdf() || $document->isImage()) ? 'inline' : 'attachment';
            
            return response($fileContent)
                ->header('Content-Type', $document->mime_type)
                ->header('Content-Disposition', $disposition . '; filename="' . $document->original_name . '"')
                ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
                
        } catch (\Exception $e) {
            // Log the error for debugging
            \Log::error('Error serving document', [
                'permission_id' => $permission->id,
                'document_id' => $document->id,
                'file_path' => $document->file_path ?? 'N/A',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Error al servir el documento: ' . $e->getMessage());
        }
    }

    /**
     * Check if user can view a permission request
     */
    private function canView(PermissionRequest $permission): bool
    {
        $user = Auth::user();

        // Verificar que hay usuario autenticado
        if (!$user) {
            return false;
        }

        // Puede ver si es suya
        if ($permission->user_id === $user->id) {
            return true;
        }

        // Cargar relación user si no está cargada
        if (!$permission->relationLoaded('user')) {
            $permission->load('user');
        }

        // Puede ver si es supervisor del solicitante
        if ($permission->user && $permission->user->immediate_supervisor_id === $user->id) {
            return true;
        }

        // Puede ver si es RRHH o Admin
        if ($user->hasRole('jefe_rrhh') || $user->hasRole('admin')) {
            return true;
        }

        return false;
    }
}