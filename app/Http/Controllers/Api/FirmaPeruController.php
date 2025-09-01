<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FirmaPeruService;
use App\Services\PdfGeneratorService;
use App\Models\PermissionRequest;
use App\Models\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class FirmaPeruController extends Controller
{
    protected $firmaPeruService;
    protected $pdfService;

    public function __construct(FirmaPeruService $firmaPeruService, PdfGeneratorService $pdfService)
    {
        $this->firmaPeruService = $firmaPeruService;
        $this->pdfService = $pdfService;
    }

    /**
     * Endpoint para obtener parámetros de firma (llamado por firmaperu.min.js)
     */
    public function getSignatureParameters(Request $request)
    {
        try {
            Log::info('Recibida petición de parámetros FIRMA PERÚ', [
                'request_data' => $request->all(),
                'method' => $request->method(),
                'content_type' => $request->header('Content-Type'),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);

            // FIRMA PERÚ envía como application/x-www-form-urlencoded
            $paramToken = $request->input('param_token');
            
            if (empty($paramToken)) {
                Log::error('param_token no encontrado en la petición', [
                    'all_input' => $request->all(),
                    'json' => $request->json()->all(),
                    'post' => $_POST ?? []
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'param_token requerido'
                ], 400);
            }

            $result = $this->firmaPeruService->handleParameterRequest($paramToken);
            
            if (!$result['success']) {
                Log::error('Error en handleParameterRequest', $result);
                return response()->json($result, 400);
            }

            // Log para debugging - verificar que los parámetros están correctos
            Log::info('Parámetros FIRMA PERÚ generados exitosamente', [
                'param_token' => $paramToken,
                'params_length' => strlen($result['params']),
                'params_preview' => substr($result['params'], 0, 100) . '...',
                'is_base64' => base64_decode($result['params'], true) !== false
            ]);

            // FIRMA PERÚ espera los parámetros como JSON codificado en Base64
            // Según documentación oficial página 8: "Debe de retornar un objeto JSON codificado en Base64"
            return response($result['params'])
                ->header('Content-Type', 'text/plain; charset=utf-8');

        } catch (\Exception $e) {
            Log::error('Error en getSignatureParameters', [
                'error' => $e->getMessage(),
                'request' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Endpoint para descargar documento original (para firma de empleado)
     */
    public function getDocument(Request $request, PermissionRequest $permission): Response
    {
        try {
            // Validar token
            $token = $request->query('token');
            $tokenData = cache()->get("firma_token_{$token}");
            
            if (!$tokenData || $tokenData['permission_id'] !== $permission->id) {
                return response('Token inválido', 403);
            }

            // Generar PDF original
            $result = $this->pdfService->generatePermissionRequestPdf($permission);
            
            if (!$result['success']) {
                return response('Error al generar PDF', 500);
            }

            return response($result['pdf']->output())
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $result['filename'] . '"');

        } catch (\Exception $e) {
            Log::error('Error al obtener documento para firma', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);

            return response('Error interno', 500);
        }
    }

    /**
     * Endpoint para descargar documento ya firmado (para firmas de nivel 2 y 3)
     */
    public function getSignedDocument(Request $request, PermissionRequest $permission): Response
    {
        try {
            // Validar token
            $token = $request->query('token');
            $tokenData = cache()->get("firma_token_{$token}");
            
            if (!$tokenData || $tokenData['permission_id'] !== $permission->id) {
                return response('Token inválido', 403);
            }

            // Obtener el documento más reciente firmado
            $latestSignature = $this->firmaPeruService->getLatestSignedDocument($permission);
            
            if (!$latestSignature || !$latestSignature->document_exists) {
                return response('Documento firmado no encontrado', 404);
            }

            $fileContent = Storage::get($latestSignature->document_path);
            $filename = basename($latestSignature->document_path);

            return response($fileContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '"');

        } catch (\Exception $e) {
            Log::error('Error al obtener documento firmado', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);

            return response('Error interno', 500);
        }
    }

    /**
     * Endpoint para recibir documento firmado desde FIRMA PERÚ
     */
    public function uploadSignedDocument(Request $request, PermissionRequest $permission): JsonResponse
    {
        try {
            Log::info('Recibiendo documento firmado de FIRMA PERÚ', [
                'permission_id' => $permission->id,
                'has_file' => $request->hasFile('signed_file'),
                'all_files' => $request->allFiles(),
                'content_type' => $request->header('Content-Type'),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
            
            $result = $this->firmaPeruService->processSignedDocument($request, $permission);
            
            if (!$result['success']) {
                Log::warning('Error procesando documento firmado', [
                    'permission_id' => $permission->id,
                    'error' => $result['message']
                ]);
                
                return response()->json($result, 400);
            }

            Log::info('Documento firmado procesado exitosamente', [
                'permission_id' => $permission->id,
                'signature_id' => $result['signature_id']
            ]);

            // Respuesta exitosa para FIRMA PERÚ
            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'signature_id' => $result['signature_id'],
                'next_stage' => $result['next_stage']
            ]);

        } catch (\Exception $e) {
            Log::error('Error al procesar documento firmado', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al procesar documento'
            ], 500);
        }
    }

    /**
     * Iniciar proceso de firma para empleado
     */
    public function initiateEmployeeSignature(PermissionRequest $permission): JsonResponse
    {
        try {
            // Verificar autorización
            if ($permission->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para firmar esta solicitud'
                ], 403);
            }

            // Verificar estado
            if ($permission->status !== PermissionRequest::STATUS_DRAFT) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud debe estar en borrador para ser firmada'
                ], 400);
            }

            $result = $this->firmaPeruService->prepareEmployeeSignatureParams($permission);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error al iniciar firma de empleado', [
                'permission_id' => $permission->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al iniciar proceso de firma'
            ], 500);
        }
    }

    /**
     * Iniciar proceso de firma para jefe inmediato (Level 1)
     */
    public function initiateLevel1Signature(PermissionRequest $permission): JsonResponse
    {
        try {
            // Verificar autorización
            if (!auth()->user()->hasRole('jefe_inmediato') && !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para aprobar esta solicitud'
                ], 403);
            }

            // Verificar estado
            if ($permission->status !== PermissionRequest::STATUS_PENDING_IMMEDIATE_BOSS) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud no está en estado de aprobación de jefe inmediato'
                ], 400);
            }

            $result = $this->firmaPeruService->prepareLevel1SignatureParams($permission);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error al iniciar firma de jefe inmediato', [
                'permission_id' => $permission->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al iniciar proceso de firma'
            ], 500);
        }
    }

    /**
     * Iniciar proceso de firma para RRHH (Level 2)
     */
    public function initiateLevel2Signature(PermissionRequest $permission): JsonResponse
    {
        try {
            // Verificar autorización
            if (!auth()->user()->hasRole('jefe_rrhh') && !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No autorizado para aprobar esta solicitud'
                ], 403);
            }

            // Verificar estado
            if ($permission->status !== PermissionRequest::STATUS_PENDING_HR) {
                return response()->json([
                    'success' => false,
                    'message' => 'La solicitud no está en estado de aprobación de RRHH'
                ], 400);
            }

            $result = $this->firmaPeruService->prepareLevel2SignatureParams($permission);
            
            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error al iniciar firma de RRHH', [
                'permission_id' => $permission->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al iniciar proceso de firma'
            ], 500);
        }
    }

    /**
     * Obtener estado de firmas de una solicitud
     */
    public function getSignatureStatus(PermissionRequest $permission): JsonResponse
    {
        try {
            $signatures = $permission->digitalSignatures()
                ->with('user')
                ->where('is_valid', true)
                ->orderBy('signed_at')
                ->get();

            $signatureData = $signatures->map(function ($signature) {
                return [
                    'id' => $signature->id,
                    'type' => $signature->signature_type,
                    'signer_name' => $signature->user->name,
                    'signer_dni' => $signature->user->dni,
                    'signed_at' => $signature->signed_at,
                    'stage' => $this->getStageNumber($signature->signature_type),
                    'integrity_valid' => $signature->verifyDocumentIntegrity()
                ];
            });

            return response()->json([
                'success' => true,
                'permission_status' => $permission->status,
                'signatures' => $signatureData,
                'total_signatures' => $signatures->count(),
                'is_fully_signed' => $this->isFullySigned($signatures),
                'next_required_signature' => $this->getNextRequiredSignature($permission)
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener estado de firmas', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al obtener estado'
            ], 500);
        }
    }

    /**
     * Verificar integridad de todas las firmas
     */
    public function verifyAllSignatures(PermissionRequest $permission): JsonResponse
    {
        try {
            $results = $this->firmaPeruService->validateAllSignatures($permission);
            
            $allValid = collect($results)->every(function ($result) {
                return $result['integrity_valid'] && $result['document_exists'];
            });

            return response()->json([
                'success' => true,
                'all_signatures_valid' => $allValid,
                'signature_details' => $results,
                'verified_at' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            Log::error('Error al verificar firmas', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno al verificar firmas'
            ], 500);
        }
    }

    /**
     * Obtener número de etapa según tipo de firma
     */
    protected function getStageNumber(string $signatureType): int
    {
        switch ($signatureType) {
            case 'employee':
                return 1;
            case 'level1_supervisor':
                return 2;
            case 'level2_hr':
                return 3;
            default:
                return 0;
        }
    }

    /**
     * Verificar si está completamente firmado
     */
    protected function isFullySigned($signatures): bool
    {
        $requiredTypes = ['employee', 'level1_supervisor', 'level2_hr'];
        $existingTypes = $signatures->pluck('signature_type')->toArray();
        
        return collect($requiredTypes)->every(function ($type) use ($existingTypes) {
            return in_array($type, $existingTypes);
        });
    }

    /**
     * Obtener siguiente firma requerida
     */
    protected function getNextRequiredSignature(PermissionRequest $permission): ?string
    {
        $hasEmployee = $permission->digitalSignatures()
            ->where('signature_type', 'employee')
            ->where('is_valid', true)
            ->exists();

        $hasLevel1 = $permission->digitalSignatures()
            ->where('signature_type', 'level1_supervisor')
            ->where('is_valid', true)
            ->exists();

        $hasLevel2 = $permission->digitalSignatures()
            ->where('signature_type', 'level2_hr')
            ->where('is_valid', true)
            ->exists();

        if (!$hasEmployee) {
            return 'employee';
        } elseif (!$hasLevel1) {
            return 'level1_supervisor';
        } elseif (!$hasLevel2) {
            return 'level2_hr';
        }

        return null; // Completamente firmado
    }
}