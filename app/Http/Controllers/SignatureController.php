<?php

namespace App\Http\Controllers;

use App\Models\PermissionRequest;
use App\Models\DigitalSignature;
use App\Services\PdfGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class SignatureController extends Controller
{
    protected $pdfService;

    public function __construct(PdfGeneratorService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Mostrar página para proceso de firma
     */
    public function showSignatureProcess(PermissionRequest $permission)
    {
        // Verificar que el usuario puede firmar esta solicitud
        if ($permission->user_id !== Auth::id()) {
            abort(403, 'No tienes autorización para firmar esta solicitud');
        }

        // Verificar que la solicitud está en estado borrador
        if ($permission->status !== 'draft') {
            return redirect()->route('permissions.show', $permission)
                ->with('error', 'Solo se pueden firmar solicitudes en estado borrador');
        }

        // Verificar si ya tiene firma digital
        $hasSignature = $permission->digitalSignatures()
            ->where('signature_type', 'onpe_employee_signature')
            ->where('is_valid', true)
            ->exists();

        return view('permissions.signature-process', compact('permission', 'hasSignature'));
    }

    /**
     * Descargar PDF sin firmar para que el usuario lo firme externamente
     */
    public function downloadUnsignedPdf(PermissionRequest $permission)
    {
        // Verificar autorización
        if ($permission->user_id !== Auth::id()) {
            abort(403, 'No tienes autorización para descargar este PDF');
        }

        // Verificar estado
        if ($permission->status !== 'draft') {
            return redirect()->route('permissions.show', $permission)
                ->with('error', 'Solo se puede descargar PDF de solicitudes en borrador');
        }

        try {
            // Generar PDF
            $result = $this->pdfService->generatePermissionRequestPdf($permission);
            
            if (!$result['success']) {
                return redirect()->back()->with('error', $result['message']);
            }

            // Log de la descarga
            Log::info('PDF descargado para firma', [
                'permission_id' => $permission->id,
                'user_id' => Auth::id(),
                'filename' => $result['filename']
            ]);

            // Descargar PDF
            return $result['pdf']->download($result['filename']);

        } catch (\Exception $e) {
            Log::error('Error al generar PDF para firma', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al generar el PDF');
        }
    }

    /**
     * Subir PDF firmado
     */
    public function uploadSignedPdf(Request $request, PermissionRequest $permission)
    {
        // Validar datos de entrada
        $request->validate([
            'signed_pdf' => 'required|file|mimes:pdf|max:10240', // 10MB máximo
            'confirm_signature' => 'required|accepted'
        ], [
            'signed_pdf.required' => 'Debe seleccionar el archivo PDF firmado',
            'signed_pdf.mimes' => 'El archivo debe ser un PDF',
            'signed_pdf.max' => 'El archivo no debe exceder 10MB',
            'confirm_signature.accepted' => 'Debe confirmar que el archivo está firmado digitalmente'
        ]);

        // Verificar autorización
        if ($permission->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes autorización para subir archivos a esta solicitud'
            ], 403);
        }

        // Verificar estado
        if ($permission->status !== 'draft') {
            return response()->json([
                'success' => false,
                'message' => 'Solo se pueden subir archivos a solicitudes en borrador'
            ], 400);
        }

        try {
            $uploadedFile = $request->file('signed_pdf');
            
            // Guardar archivo firmado
            $saveResult = $this->pdfService->saveSignedPdf($permission, $uploadedFile);
            
            if (!$saveResult['success']) {
                return response()->json([
                    'success' => false,
                    'message' => $saveResult['message']
                ], 400);
            }

            // Validar PDF firmado
            $validationResult = $this->pdfService->validateSignedPdf($saveResult['path']);
            
            // Crear registro de firma digital (aunque la validación falle, guardamos el intento)
            $signature = $this->createSignatureRecord($permission, $saveResult, $validationResult);

            // Log del proceso
            Log::info('PDF firmado subido', [
                'permission_id' => $permission->id,
                'user_id' => Auth::id(),
                'file_path' => $saveResult['path'],
                'validation_success' => $validationResult['success']
            ]);

            if ($validationResult['success'] || ($validationResult['warning'] ?? false)) {
                // Actualizar estado de la solicitud
                $permission->update([
                    'status' => 'pending_immediate_boss',
                    'signed_at' => now()
                ]);

                $message = $validationResult['success'] 
                    ? 'PDF firmado subido exitosamente' 
                    : 'PDF subido con advertencias: ' . $validationResult['message'];

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'warning' => $validationResult['warning'] ?? false,
                    'can_submit' => true,
                    'redirect_url' => route('permissions.show', $permission)
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Error en validación: ' . $validationResult['message'],
                    'can_retry' => true
                ], 400);
            }

        } catch (\Exception $e) {
            Log::error('Error al procesar PDF firmado', [
                'permission_id' => $permission->id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar el archivo'
            ], 500);
        }
    }

    /**
     * Enviar solicitud firmada al siguiente nivel de aprobación
     */
    public function submitSignedRequest(PermissionRequest $permission)
    {
        // Verificar autorización
        if ($permission->user_id !== Auth::id()) {
            abort(403, 'No tienes autorización para enviar esta solicitud');
        }

        // Verificar que está firmada
        if ($permission->status !== 'firmado') {
            return redirect()->route('permissions.show', $permission)
                ->with('error', 'La solicitud debe estar firmada antes de enviarla');
        }

        // Verificar que tiene firma digital válida
        if (!$permission->digitalSignatures()->where('signature_type', 'onpe_employee_signature')->where('is_valid', true)->exists()) {
            return redirect()->route('permissions.show', $permission)
                ->with('error', 'La solicitud no tiene una firma digital válida');
        }

        try {
            // Cambiar estado a pendiente jefe inmediato
            $permission->update([
                'status' => 'pendiente_jefe_inmediato',
                'submitted_at' => now()
            ]);

            // Log del envío
            Log::info('Solicitud firmada enviada', [
                'permission_id' => $permission->id,
                'user_id' => Auth::id(),
                'submitted_at' => now()
            ]);

            // TODO: Enviar notificación al jefe inmediato

            return redirect()->route('permissions.show', $permission)
                ->with('success', 'Solicitud enviada exitosamente al jefe inmediato para su aprobación');

        } catch (\Exception $e) {
            Log::error('Error al enviar solicitud firmada', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al enviar la solicitud');
        }
    }

    /**
     * Descargar documento firmado
     */
    public function downloadSignedPdf(PermissionRequest $permission)
    {
        // Verificar autorización (empleado, jefes o admin pueden descargar)
        if ($permission->user_id !== Auth::id() && 
            !Auth::user()->hasAnyRole(['jefe_inmediato', 'jefe_rrhh', 'admin'])) {
            abort(403, 'No tienes autorización para descargar este documento');
        }

        // Buscar la firma digital
        $signature = $permission->digitalSignatures()
            ->where('signature_type', 'onpe_employee_signature')
            ->where('is_valid', true)
            ->first();

        if (!$signature || !$signature->document_path) {
            abort(404, 'Documento firmado no encontrado');
        }

        // Verificar que el archivo existe
        if (!Storage::exists($signature->document_path)) {
            abort(404, 'El archivo firmado no está disponible');
        }

        $fileName = 'solicitud_firmada_' . $permission->request_number . '.pdf';
        
        return Storage::download($signature->document_path, $fileName);
    }

    /**
     * Remover firma digital (para volver a borrador)
     */
    public function removeSignature(PermissionRequest $permission)
    {
        // Verificar autorización
        if ($permission->user_id !== Auth::id()) {
            abort(403, 'No tienes autorización para modificar esta solicitud');
        }

        // Verificar estado
        if (!in_array($permission->status, ['firmado'])) {
            return redirect()->back()->with('error', 'Solo se puede remover la firma de solicitudes firmadas');
        }

        try {
            // Marcar firmas como inválidas
            $permission->digitalSignatures()
                ->where('signature_type', 'onpe_employee_signature')
                ->update([
                    'is_valid' => false,
                    'signature_metadata->invalidated_at' => now(),
                    'signature_metadata->invalidation_reason' => 'Removida por el usuario'
                ]);

            // Cambiar estado a borrador
            $permission->update([
                'status' => 'borrador',
                'signed_at' => null
            ]);

            Log::info('Firma digital removida', [
                'permission_id' => $permission->id,
                'user_id' => Auth::id()
            ]);

            return redirect()->route('permissions.show', $permission)
                ->with('success', 'Firma digital removida. La solicitud ha vuelto a estado borrador');

        } catch (\Exception $e) {
            Log::error('Error al remover firma digital', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Error al remover la firma');
        }
    }

    /**
     * Verificar información de la firma
     */
    public function verifySignature(PermissionRequest $permission)
    {
        $signature = $permission->digitalSignatures()
            ->where('signature_type', 'onpe_employee_signature')
            ->first();

        if (!$signature) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró firma digital'
            ]);
        }

        // Obtener información del archivo
        $pdfInfo = $this->pdfService->getSignedPdfInfo($signature->document_path);
        
        // Verificar integridad
        $integrityCheck = false;
        if ($pdfInfo && $signature->signature_hash) {
            $integrityCheck = $pdfInfo['file_hash'] === $signature->signature_hash;
        }

        return response()->json([
            'success' => true,
            'signature_info' => [
                'signed_at' => $signature->signed_at,
                'signer' => $permission->user->full_name,
                'signer_dni' => $permission->user->dni,
                'document_hash' => $signature->signature_hash,
                'file_size' => $pdfInfo['file_size'] ?? 0,
                'integrity_check' => $integrityCheck ? 'VÁLIDO' : 'COMPROMETIDO',
                'is_valid' => $signature->is_valid && $integrityCheck
            ]
        ]);
    }

    /**
     * Crear registro de firma digital
     */
    protected function createSignatureRecord(PermissionRequest $permission, array $saveResult, array $validationResult)
    {
        return DigitalSignature::create([
            'permission_request_id' => $permission->id,
            'user_id' => $permission->user_id,
            'signature_type' => 'onpe_employee_signature',
            'signature_hash' => $saveResult['hash'],
            'signed_at' => now(),
            'document_path' => $saveResult['path'],
            'is_valid' => $validationResult['success'],
            'signature_metadata' => [
                'original_filename' => $saveResult['filename'],
                'file_size' => $saveResult['size'],
                'validation_result' => $validationResult,
                'upload_ip' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]
        ]);
    }
}