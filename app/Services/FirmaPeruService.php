<?php

namespace App\Services;

use App\Models\PermissionRequest;
use App\Models\DigitalSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class FirmaPeruService
{
    protected $clientId;
    protected $clientSecret;
    protected $tokenUrl;
    protected $jsUrl = 'https://apps.firmaperu.gob.pe/web/clienteweb/firmaperu.min.js';
    protected $port = 48596; // Puerto recomendado por la documentación

    public function __construct()
    {
        $this->loadCredentials();
    }

    /**
     * Cargar credenciales desde el archivo de configuración
     */
    protected function loadCredentials()
    {
        $configPath = base_path('fwAuthorization.json');
        
        if (!file_exists($configPath)) {
            throw new \Exception('Archivo de configuración FIRMA PERÚ no encontrado');
        }

        $config = json_decode(file_get_contents($configPath), true);
        
        if (!$config) {
            throw new \Exception('Error al leer configuración FIRMA PERÚ');
        }

        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->tokenUrl = $config['token_url'];
    }

    /**
     * Generar token de acceso para FIRMA PERÚ
     */
    public function generateToken(): array
    {
        try {
            // Log para verificar configuración
            Log::info('Generando token FIRMA PERÚ', [
                'client_id' => $this->clientId,
                'client_secret_length' => strlen($this->clientSecret ?? ''),
                'token_url' => $this->tokenUrl
            ]);

            $response = Http::withOptions([
                'verify' => env('FIRMA_PERU_VERIFY_SSL', app()->environment('production')),
                'timeout' => 30,
                'connect_timeout' => 10,
            ])->asForm()->post($this->tokenUrl, [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if (!$response->successful()) {
                Log::error('Error al generar token FIRMA PERÚ', [
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Error al conectar con FIRMA PERÚ'
                ];
            }

            // FIRMA PERÚ devuelve directamente el JWT token, no un JSON
            $token = trim($response->body());
            
            // Decodificar JWT para obtener información de expiración
            $jwtPayload = $this->decodeJwtPayload($token);
            $expiresIn = null;
            
            if ($jwtPayload && isset($jwtPayload['exp'])) {
                $expiresIn = $jwtPayload['exp'] - time();
            }
            
            // Log detallado para debugging
            Log::info('Token JWT generado exitosamente', [
                'status_code' => $response->status(),
                'content_type' => $response->header('Content-Type'),
                'token_length' => strlen($token),
                'token_preview' => substr($token, 0, 50) . '...',
                'expires_in_seconds' => $expiresIn,
                'jwt_payload_preview' => $jwtPayload ? [
                    'iss' => $jwtPayload['iss'] ?? null,
                    'exp' => $jwtPayload['exp'] ?? null,
                    'iat' => $jwtPayload['iat'] ?? null
                ] : null
            ]);
            
            // Validar que el token tenga el formato JWT
            if (empty($token) || substr_count($token, '.') !== 2) {
                Log::error('Token JWT inválido recibido de FIRMA PERÚ', [
                    'token_length' => strlen($token),
                    'raw_response' => $response->body()
                ]);
                
                return [
                    'success' => false,
                    'message' => 'Token JWT inválido recibido del servicio de firma'
                ];
            }

            return [
                'success' => true,
                'token' => $token,
                'expires_in' => $expiresIn ?? 3600 // Usar expiración del JWT o valor por defecto
            ];

        } catch (\Exception $e) {
            Log::error('Excepción al generar token FIRMA PERÚ', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al generar token de firma'
            ];
        }
    }

    /**
     * Preparar parámetros de firma para empleado (Stage 1)
     */
    public function prepareEmployeeSignatureParams(PermissionRequest $permission): array
    {
        $tokenResult = $this->generateToken();
        
        if (!$tokenResult['success']) {
            return $tokenResult;
        }

        $paramToken = Str::random(32);
        $documentUrl = url('/api/firma-peru/document/' . $permission->id) . '?token=' . $paramToken;
        
        // Almacenar token temporalmente para validación posterior
        $currentUserId = auth()->id();
        
        Log::info('Creando token para firma de empleado', [
            'permission_id' => $permission->id,
            'current_user_id' => $currentUserId,
            'auth_check' => auth()->check(),
            'auth_user' => auth()->user() ? auth()->user()->id : null
        ]);
        
        $tokenData = [
            'permission_id' => $permission->id,
            'user_id' => $currentUserId,
            'signature_type' => 'employee',
            'expires_at' => now()->addMinutes(30),
            'encoded_params' => null // Se almacenará después de generar los parámetros
        ];
        cache()->put("firma_token_{$paramToken}", $tokenData, 1800); // 30 minutos
        $this->storeActiveSignatureIndex($paramToken, $tokenData);

        // Obtener posición automática según el tipo de usuario
        $signaturePosition = $this->getSignaturePosition('employee');
        
        $params = [
            'signatureFormat' => 'PAdES',
            'signatureLevel' => 'B',
            'signaturePackaging' => 'enveloped',
            'documentToSign' => $documentUrl,
            'certificateFilter' => '.*', // Detectar todos los certificados disponibles
            'theme' => 'claro',
            'visiblePosition' => false, // Desactivar selección manual para usar posiciones automáticas
            'signatureReason' => 'Firma de solicitud de permiso por empleado',
            'bachtOperation' => false,
            'oneByOne' => true,
            'signatureStyle' => 4, // Solo descripción, sin imagen para evitar error "no protocol"
            'stampTextSize' => 14,
            'stampWordWrap' => 37,
            'role' => $signaturePosition['role'],
            'stampPage' => 1,
            'positionx' => $signaturePosition['positionx'],
            'positiony' => $signaturePosition['positiony'],
            'uploadDocumentSigned' => url('/api/firma-peru/upload/' . $permission->id),
            'certificationSignature' => false,
            'token' => $tokenResult['token']
        ];

        // Omitir campos opcionales vacíos para evitar errores "no protocol:"
        // Según documentación, estos campos deben omitirse si están vacíos

        // Log para debugging - verificar parámetros antes de codificar
        Log::info('Parámetros de firma generados (employee)', [
            'documentToSign' => $params['documentToSign'],
            'uploadDocumentSigned' => $params['uploadDocumentSigned'],
            'signatureReason' => $params['signatureReason'],
            'role' => $params['role'],
            'signatureFormat' => $params['signatureFormat'],
            'signatureStyle' => $params['signatureStyle'],
            'positionx' => $params['positionx'],
            'positiony' => $params['positiony'],
            'stampPage' => $params['stampPage'],
            'visiblePosition' => $params['visiblePosition']
        ]);

        // Codificar los parámetros en JSON y luego en Base64 según documentación
        $encodedParams = base64_encode(json_encode($params));
        
        // Actualizar el caché con los parámetros codificados
        $tokenData['encoded_params'] = $encodedParams;
        cache()->put("firma_token_{$paramToken}", $tokenData, 1800);
        $this->storeActiveSignatureIndex($paramToken, $tokenData);
        
        return [
            'success' => true,
            'params' => $encodedParams,
            'param_token' => $paramToken,
            'js_url' => $this->jsUrl,
            'port' => $this->port
        ];
    }

    // /**
    //  * Preparar parámetros de firma para jefe inmediato (Stage 2)
    //  */
    // public function prepareLevel1SignatureParams(PermissionRequest $permission): array
    // {
    //     $tokenResult = $this->generateToken();
        
    //     if (!$tokenResult['success']) {
    //         return $tokenResult;
    //     }

    //     // Verificar que existe firma del empleado
    //     if (!$this->hasValidEmployeeSignature($permission)) {
    //         return [
    //             'success' => false,
    //             'message' => 'La solicitud debe estar firmada por el empleado primero'
    //         ];
    //     }

    //     $paramToken = Str::random(32);
    //     $documentUrl = url('/api/firma-peru/signed-document/' . $permission->id) . '?token=' . $paramToken;
        
    //     $currentUserId = auth()->id();
        
    //     Log::info('Creando token para firma level1', [
    //         'permission_id' => $permission->id,
    //         'current_user_id' => $currentUserId,
    //         'auth_check' => auth()->check(),
    //         'auth_user' => auth()->user() ? auth()->user()->id : null
    //     ]);
        
    //     $tokenData = [
    //         'permission_id' => $permission->id,
    //         'user_id' => $currentUserId,
    //         'signature_type' => 'level1_supervisor',
    //         'expires_at' => now()->addMinutes(30),
    //         'encoded_params' => null // Se almacenará después de generar los parámetros
    //     ];
    //     cache()->put("firma_token_{$paramToken}", $tokenData, 1800);
    //     $this->storeActiveSignatureIndex($paramToken, $tokenData);

    //     // Obtener posición automática según el tipo de usuario
    //     $signaturePosition = $this->getSignaturePosition('level1_supervisor');
        
    //     $params = [
    //         'signatureFormat' => 'PAdES',
    //         'signatureLevel' => 'B',
    //         'signaturePackaging' => 'enveloped',
    //         'documentToSign' => $documentUrl,
    //         'certificateFilter' => '.*', // Detectar todos los certificados disponibles
    //         'theme' => 'claro',
    //         'visiblePosition' => false, // Desactivar selección manual para usar posiciones automáticas
    //         'signatureReason' => 'Aprobación de solicitud de permiso - Jefe Inmediato',
    //         'bachtOperation' => false,
    //         'oneByOne' => true,
    //         'signatureStyle' => 4, // Solo descripción, sin imagen para evitar error "no protocol"
    //         'stampTextSize' => 14,
    //         'stampWordWrap' => 37,
    //         'role' => $signaturePosition['role'],
    //         'stampPage' => 1,
    //         'positionx' => $signaturePosition['positionx'],
    //         'positiony' => $signaturePosition['positiony'],
    //         'uploadDocumentSigned' => url('/api/firma-peru/upload/' . $permission->id),
    //         'certificationSignature' => false,
    //         'token' => $tokenResult['token']
    //     ];

    //     // Log para debugging - verificar parámetros antes de codificar
    //     Log::info('Parámetros de firma generados (level1)', [
    //         'documentToSign' => $params['documentToSign'],
    //         'uploadDocumentSigned' => $params['uploadDocumentSigned'],
    //         'signatureReason' => $params['signatureReason'],
    //         'role' => $params['role'],
    //         'signatureFormat' => $params['signatureFormat'],
    //         'signatureStyle' => $params['signatureStyle'],
    //         'positionx' => $params['positionx'],
    //         'positiony' => $params['positiony'],
    //         'stampPage' => $params['stampPage'],
    //         'visiblePosition' => $params['visiblePosition']
    //     ]);

    //     // Codificar los parámetros en JSON y luego en Base64 según documentación
    //     $encodedParams = base64_encode(json_encode($params));
        
    //     // Actualizar el caché con los parámetros codificados
    //     $tokenData['encoded_params'] = $encodedParams;
    //     cache()->put("firma_token_{$paramToken}", $tokenData, 1800);
    //     $this->storeActiveSignatureIndex($paramToken, $tokenData);
        
    //     return [
    //         'success' => true,
    //         'params' => $encodedParams,
    //         'param_token' => $paramToken,
    //         'js_url' => $this->jsUrl,
    //         'port' => $this->port
    //     ];
    // }

    /**
     * Preparar parámetros de firma para jefe inmediato (Stage 2)
     */
    public function prepareLevel1SignatureParams(PermissionRequest $permission): array
    {
        $tokenResult = $this->generateToken();
        
        if (!$tokenResult['success']) {
            return $tokenResult;
        }

        // Verificar si existe firma del empleado para determinar qué documento usar
        $hasEmployeeSignature = $this->hasValidEmployeeSignature($permission);

        $paramToken = Str::random(32);

        // Si tiene firma del empleado, usar documento firmado; si no, usar documento base
        if ($hasEmployeeSignature) {
            $documentUrl = url('/api/firma-peru/signed-document/' . $permission->id) . '?token=' . $paramToken;
        } else {
            // Permiso enviado sin firma del empleado, usar documento base
            $documentUrl = url('/api/firma-peru/document/' . $permission->id) . '?token=' . $paramToken;
        }
        
        $currentUserId = auth()->id();
        $currentUser = auth()->user();
        
        // Detectar si es caso especial: RRHH firmando en nivel 1
        $skipImmediateSupervisor = $permission->metadata['skip_immediate_supervisor'] ?? false;
        $isHrSigningLevel1 = $currentUser->hasRole('jefe_rrhh') && $skipImmediateSupervisor;
        
        Log::info('Creando token para firma level1', [
            'permission_id' => $permission->id,
            'current_user_id' => $currentUserId,
            'is_hr_special_case' => $isHrSigningLevel1,
            'skip_immediate_supervisor' => $skipImmediateSupervisor,
            'auth_check' => auth()->check(),
            'auth_user' => auth()->user() ? auth()->user()->id : null
        ]);
        
        $tokenData = [
            'permission_id' => $permission->id,
            'user_id' => $currentUserId,
            'signature_type' => 'level1_supervisor',
            'is_hr_special_case' => $isHrSigningLevel1,
            'expires_at' => now()->addMinutes(30),
            'encoded_params' => null
        ];
        cache()->put("firma_token_{$paramToken}", $tokenData, 1800);
        $this->storeActiveSignatureIndex($paramToken, $tokenData);

        // Determinar posición de firma y motivo
        if ($isHrSigningLevel1) {
            // Caso especial: RRHH firma en nivel 1 (jefe no disponible)
            // RRHH siempre mantiene su posición a la derecha, independiente de si hay firma del empleado
            $signaturePositionType = 'level2_hr';
            $signatureReason = $hasEmployeeSignature
                ? 'Aprobación de solicitud de permiso - RRHH (Nivel 1 - Jefe Inmediato No Disponible)'
                : 'Aprobación de solicitud - RRHH (Nivel 1 - Sin firma del empleado)';
        } elseif (!$hasEmployeeSignature) {
            // Si no hay firma del empleado, el jefe firma en la posición del empleado (izquierda)
            $signaturePositionType = 'level1_supervisor';
            $signatureReason = 'Aprobación de solicitud - Jefe Inmediato (Sin firma del empleado)';
        } else {
            // Flujo normal: Jefe Inmediato firma en su posición (centro)
            $signaturePositionType = 'level1_supervisor';
            $signatureReason = 'Aprobación de solicitud de permiso - Jefe Inmediato';
        }

        $signaturePosition = $this->getSignaturePosition($signaturePositionType);
        
        $params = [
            'signatureFormat' => 'PAdES',
            'signatureLevel' => 'B',
            'signaturePackaging' => 'enveloped',
            'documentToSign' => $documentUrl,
            'certificateFilter' => '.*',
            'theme' => 'claro',
            'visiblePosition' => false,
            'signatureReason' => $signatureReason,
            'bachtOperation' => false,
            'oneByOne' => true,
            'signatureStyle' => 4,
            'stampTextSize' => 14,
            'stampWordWrap' => 37,
            'role' => $signaturePosition['role'],
            'stampPage' => 1,
            'positionx' => $signaturePosition['positionx'],
            'positiony' => $signaturePosition['positiony'],
            'uploadDocumentSigned' => url('/api/firma-peru/upload/' . $permission->id),
            'certificationSignature' => false,
            'token' => $tokenResult['token']
        ];

        Log::info('Parámetros de firma generados (level1)', [
            'documentToSign' => $params['documentToSign'],
            'uploadDocumentSigned' => $params['uploadDocumentSigned'],
            'signatureReason' => $params['signatureReason'],
            'role' => $params['role'],
            'signatureFormat' => $params['signatureFormat'],
            'signatureStyle' => $params['signatureStyle'],
            'positionx' => $params['positionx'],
            'positiony' => $params['positiony'],
            'stampPage' => $params['stampPage'],
            'visiblePosition' => $params['visiblePosition'],
            'is_hr_special_case' => $isHrSigningLevel1,
            'signature_position_type' => $signaturePositionType
        ]);

        $encodedParams = base64_encode(json_encode($params));
        
        $tokenData['encoded_params'] = $encodedParams;
        cache()->put("firma_token_{$paramToken}", $tokenData, 1800);
        $this->storeActiveSignatureIndex($paramToken, $tokenData);
        
        return [
            'success' => true,
            'params' => $encodedParams,
            'param_token' => $paramToken,
            'js_url' => $this->jsUrl,
            'port' => $this->port
        ];
    }

    /**
     * Preparar parámetros de firma para RRHH (Stage 3)
     */
    public function prepareLevel2SignatureParams(PermissionRequest $permission): array
    {
        $tokenResult = $this->generateToken();
        
        if (!$tokenResult['success']) {
            return $tokenResult;
        }

        // Verificar que existen firmas previas
        if (!$this->hasValidLevel1Signature($permission)) {
            return [
                'success' => false,
                'message' => 'La solicitud debe estar firmada por empleado y jefe inmediato primero'
            ];
        }

        $paramToken = Str::random(32);
        $documentUrl = url('/api/firma-peru/signed-document/' . $permission->id) . '?token=' . $paramToken;
        
        $currentUserId = auth()->id();
        
        Log::info('Creando token para firma level2', [
            'permission_id' => $permission->id,
            'current_user_id' => $currentUserId,
            'auth_check' => auth()->check(),
            'auth_user' => auth()->user() ? auth()->user()->id : null
        ]);
        
        $tokenData = [
            'permission_id' => $permission->id,
            'user_id' => $currentUserId,
            'signature_type' => 'level2_hr',
            'expires_at' => now()->addMinutes(30),
            'encoded_params' => null // Se almacenará después de generar los parámetros
        ];
        cache()->put("firma_token_{$paramToken}", $tokenData, 1800);
        $this->storeActiveSignatureIndex($paramToken, $tokenData);

        // Obtener posición automática según el tipo de usuario
        $signaturePosition = $this->getSignaturePosition('level2_hr');
        
        $params = [
            'signatureFormat' => 'PAdES',
            'signatureLevel' => 'B',
            'signaturePackaging' => 'enveloped',
            'documentToSign' => $documentUrl,
            'certificateFilter' => '.*', // Detectar todos los certificados disponibles
            'theme' => 'claro',
            'visiblePosition' => false, // Desactivar selección manual para usar posiciones automáticas
            'signatureReason' => 'Aprobación final de solicitud de permiso - RRHH',
            'bachtOperation' => false,
            'oneByOne' => true,
            'signatureStyle' => 4, // Solo descripción, sin imagen para evitar error "no protocol"
            'stampTextSize' => 14,
            'stampWordWrap' => 37,
            'role' => $signaturePosition['role'],
            'stampPage' => 1,
            'positionx' => $signaturePosition['positionx'],
            'positiony' => $signaturePosition['positiony'],
            'uploadDocumentSigned' => url('/api/firma-peru/upload/' . $permission->id),
            'certificationSignature' => false,
            'token' => $tokenResult['token']
        ];

        // Log para debugging - verificar parámetros antes de codificar
        Log::info('Parámetros de firma generados (level2)', [
            'documentToSign' => $params['documentToSign'],
            'uploadDocumentSigned' => $params['uploadDocumentSigned'],
            'signatureReason' => $params['signatureReason'],
            'role' => $params['role'],
            'signatureFormat' => $params['signatureFormat'],
            'signatureStyle' => $params['signatureStyle'],
            'positionx' => $params['positionx'],
            'positiony' => $params['positiony'],
            'stampPage' => $params['stampPage'],
            'visiblePosition' => $params['visiblePosition']
        ]);

        // Codificar los parámetros en JSON y luego en Base64 según documentación
        $encodedParams = base64_encode(json_encode($params));
        
        // Actualizar el caché con los parámetros codificados
        $tokenData['encoded_params'] = $encodedParams;
        cache()->put("firma_token_{$paramToken}", $tokenData, 1800);
        $this->storeActiveSignatureIndex($paramToken, $tokenData);
        
        return [
            'success' => true,
            'params' => $encodedParams,
            'param_token' => $paramToken,
            'js_url' => $this->jsUrl,
            'port' => $this->port
        ];
    }

    /**
     * Manejar la recepción de parámetros desde FIRMA PERÚ
     */
    public function handleParameterRequest(string $paramToken): array
    {
        try {
            $tokenData = cache()->get("firma_token_{$paramToken}");
            
            if (!$tokenData) {
                Log::info('Token no encontrado en cache', ['param_token' => $paramToken]);
                return [
                    'success' => false,
                    'message' => 'Token inválido o expirado'
                ];
            }

        // Verificar expiración
        if (now()->isAfter($tokenData['expires_at'])) {
            cache()->forget("firma_token_{$paramToken}");
            return [
                'success' => false,
                'message' => 'Token expirado'
            ];
        }

        $permission = PermissionRequest::find($tokenData['permission_id']);
        if (!$permission) {
            return [
                'success' => false,
                'message' => 'Solicitud no encontrada'
            ];
        }

        // Verificar que los parámetros estén almacenados en caché
        if (empty($tokenData['encoded_params'])) {
            Log::error('Parámetros no encontrados en caché', [
                'param_token' => $paramToken,
                'token_data' => $tokenData
            ]);
            return [
                'success' => false,
                'message' => 'Parámetros de firma no encontrados'
            ];
        }

        // Devolver los parámetros que ya fueron preparados y almacenados
        Log::info('Devolviendo parámetros desde caché', [
            'param_token' => $paramToken,
            'signature_type' => $tokenData['signature_type'],
            'permission_id' => $tokenData['permission_id'],
            'params_length' => strlen($tokenData['encoded_params'])
        ]);

        return [
            'success' => true,
            'params' => $tokenData['encoded_params']
        ];
        } catch (\Exception $e) {
            Log::error('Error en handleParameterRequest', [
                'param_token' => $paramToken,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error interno al procesar parámetros'
            ];
        }
    }

    /**
     * Procesar documento firmado recibido de FIRMA PERÚ
     */
    public function processSignedDocument(Request $request, PermissionRequest $permission): array
    {
        try {
            if (!$request->hasFile('signed_file')) {
                return [
                    'success' => false,
                    'message' => 'Archivo firmado no encontrado'
                ];
            }

            $signedFile = $request->file('signed_file');
            
            // Validar que es un PDF
            if ($signedFile->getMimeType() !== 'application/pdf') {
                return [
                    'success' => false,
                    'message' => 'El archivo debe ser un PDF'
                ];
            }

            // Determinar usuario y tipo de firma desde tokens activos en caché
            $signatureData = $this->getActiveSignatureFromCache($permission);
            
            if (!$signatureData) {
                // Como última alternativa, intentar determinar por el estado de la solicitud
                $fallbackData = $this->determineFallbackSignatureData($permission);
                
                if (!$fallbackData) {
                    return [
                        'success' => false,
                        'message' => 'No se encontró un proceso de firma activo para esta solicitud'
                    ];
                }
                
                $signatureData = $fallbackData;
            }
            
            $userId = $signatureData['user_id'];
            $signatureType = $signatureData['signature_type'];
            
            // Validar que user_id no sea null
            if (!$userId) {
                Log::warning('user_id es null en datos de caché, usando fallback', [
                    'permission_id' => $permission->id,
                    'signature_data' => $signatureData
                ]);
                
                $fallbackData = $this->determineFallbackSignatureData($permission);
                if ($fallbackData && $fallbackData['user_id']) {
                    $userId = $fallbackData['user_id'];
                    $signatureType = $fallbackData['signature_type'];
                    
                    Log::info('Usando datos de fallback', [
                        'permission_id' => $permission->id,
                        'fallback_user_id' => $userId,
                        'fallback_signature_type' => $signatureType
                    ]);
                } else {
                    Log::error('No se pudo determinar user_id ni con fallback', [
                        'permission_id' => $permission->id
                    ]);
                    
                    return [
                        'success' => false,
                        'message' => 'No se pudo determinar el usuario para la firma'
                    ];
                }
            }
            
            // Generar nombre único para el archivo
            $filename = $this->generateSignedFilename($permission, $signatureType);
            $directory = 'private/signed-documents/' . now()->format('Y-m');
            
            // Guardar archivo
            $path = $signedFile->storeAs($directory, $filename);
            
            if (!$path) {
                return [
                    'success' => false,
                    'message' => 'Error al guardar el documento firmado'
                ];
            }

            // Calcular hash del archivo
            $fileHash = hash_file('sha256', Storage::path($path));
            
            // Crear registro de firma digital
            $signature = DigitalSignature::create([
                'permission_request_id' => $permission->id,
                'user_id' => $userId,
                'signature_type' => $signatureType,
                'signature_hash' => $fileHash,
                'signed_at' => now(),
                'document_path' => $path,
                'is_valid' => true,
                'signature_metadata' => [
                    'original_filename' => $signedFile->getClientOriginalName(),
                    'file_size' => $signedFile->getSize(),
                    'upload_ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'signature_stage' => $this->getSignatureStage($signatureType),
                    'firma_peru_timestamp' => now()->toISOString()
                ]
            ]);

            // Actualizar estado de la solicitud
            $this->updatePermissionStatus($permission, $signatureType);

            // Limpiar tokens de caché ya que el proceso se completó
            $this->clearActiveSignatureFromCache($permission);

            Log::info('Documento firmado procesado exitosamente', [
                'permission_id' => $permission->id,
                'user_id' => $userId,
                'signature_type' => $signatureType,
                'signature_id' => $signature->id,
                'file_path' => $path
            ]);

            return [
                'success' => true,
                'message' => 'Documento firmado correctamente',
                'signature_id' => $signature->id,
                'next_stage' => $this->getNextStage($signatureType)
            ];

        } catch (\Exception $e) {
            Log::error('Error al procesar documento firmado', [
                'permission_id' => $permission->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error interno al procesar el documento'
            ];
        }
    }

    /**
     * Obtener datos de firma activa desde caché
     */
    protected function getActiveSignatureFromCache(PermissionRequest $permission): ?array
    {
        // Primero buscar por el índice que creamos
        $indexKey = "active_signature_permission_{$permission->id}";
        $activeSignatureData = cache()->get($indexKey);
        
        Log::info('Buscando firma activa en caché', [
            'permission_id' => $permission->id,
            'index_key' => $indexKey,
            'data_found' => $activeSignatureData ? 'yes' : 'no',
            'data_preview' => $activeSignatureData ? array_keys($activeSignatureData) : null
        ]);
        
        if ($activeSignatureData && 
            isset($activeSignatureData['expires_at']) && 
            now()->isBefore($activeSignatureData['expires_at'])) {
            
            Log::info('Datos de firma encontrados en caché', [
                'permission_id' => $permission->id,
                'user_id' => $activeSignatureData['user_id'] ?? null,
                'signature_type' => $activeSignatureData['signature_type'] ?? null,
                'expires_at' => $activeSignatureData['expires_at'] ?? null
            ]);
            
            return $activeSignatureData;
        }
        
        // Si no encontramos en el índice, buscar manualmente en todos los tokens
        Log::warning('Índice de firma no encontrado, buscando manualmente', [
            'permission_id' => $permission->id
        ]);
        
        return $this->searchSignatureInAllTokens($permission);
    }
    
    /**
     * Buscar firma activa en todos los tokens (método de respaldo)
     */
    protected function searchSignatureInAllTokens(PermissionRequest $permission): ?array
    {
        // Este método es para casos de emergencia donde el índice no funcione
        // En la práctica, deberíamos usar el método fallback
        Log::warning('Método de búsqueda manual no implementado, usando fallback', [
            'permission_id' => $permission->id
        ]);
        
        return null;
    }
    
    /**
     * Determinar datos de firma como fallback cuando no hay caché
     */
    protected function determineFallbackSignatureData(PermissionRequest $permission): ?array
    {
        Log::warning('Usando método fallback para determinar datos de firma', [
            'permission_id' => $permission->id,
            'permission_status' => $permission->status,
            'permission_user_id' => $permission->user_id
        ]);
        
        // Determinar qué tipo de firma se esperaría según el estado actual
        $expectedSignatureType = null;
        $expectedUserId = null;
        
        switch ($permission->status) {
            case 'draft':
                // Si está en draft, probablemente sea firma del empleado
                $expectedSignatureType = 'employee';
                $expectedUserId = $permission->user_id;
                break;
                
            case 'pending_immediate_boss':
                // Si está pendiente de jefe inmediato, probablemente sea firma level 1
                $expectedSignatureType = 'level1_supervisor';
                // Necesitaríamos determinar quién es el jefe inmediato
                // Por ahora, buscaremos al usuario con rol de jefe inmediato
                $expectedUserId = $this->findImmediateBossUser();
                break;
                
            case 'pending_hr':
                // Si está pendiente de RRHH, probablemente sea firma level 2
                $expectedSignatureType = 'level2_hr';
                // Buscar usuario con rol de RRHH
                $expectedUserId = $this->findHRUser();
                break;
                
            default:
                Log::error('Estado de solicitud no esperado para fallback', [
                    'permission_id' => $permission->id,
                    'status' => $permission->status
                ]);
                return null;
        }
        
        if (!$expectedUserId) {
            Log::error('No se pudo determinar user_id para fallback', [
                'permission_id' => $permission->id,
                'signature_type' => $expectedSignatureType
            ]);
            return null;
        }
        
        Log::info('Datos de firma determinados por fallback', [
            'permission_id' => $permission->id,
            'user_id' => $expectedUserId,
            'signature_type' => $expectedSignatureType
        ]);
        
        return [
            'permission_id' => $permission->id,
            'user_id' => $expectedUserId,
            'signature_type' => $expectedSignatureType,
            'expires_at' => now()->addMinutes(30)
        ];
    }
    
    /**
     * Encontrar usuario con rol de jefe inmediato
     */
    protected function findImmediateBossUser(): ?int
    {
        // Buscar al primer usuario con rol de jefe inmediato
        $user = \App\Models\User::whereHas('role', function($query) {
            $query->where('name', 'jefe_inmediato');
        })->first();
        
        return $user ? $user->id : null;
    }
    
    /**
     * Encontrar usuario con rol de RRHH
     */
    protected function findHRUser(): ?int
    {
        // Buscar al primer usuario con rol de RRHH
        $user = \App\Models\User::whereHas('role', function($query) {
            $query->where('name', 'jefe_rrhh');
        })->first();
        
        return $user ? $user->id : null;
    }
    
    /**
     * Almacenar índice de firma activa para facilitar búsqueda
     */
    protected function storeActiveSignatureIndex(string $paramToken, array $tokenData): void
    {
        $indexKey = "active_signature_permission_{$tokenData['permission_id']}";
        cache()->put($indexKey, $tokenData, 1800); // 30 minutos
    }
    
    /**
     * Limpiar firma activa del caché
     */
    protected function clearActiveSignatureFromCache(PermissionRequest $permission): void
    {
        $indexKey = "active_signature_permission_{$permission->id}";
        cache()->forget($indexKey);
    }

    /**
     * Verificar si tiene firma válida del empleado
     */
    protected function hasValidEmployeeSignature(PermissionRequest $permission): bool
    {
        return $permission->digitalSignatures()
            ->where('signature_type', 'employee')
            ->where('is_valid', true)
            ->exists();
    }

    /**
     * Verificar si tiene firma válida de nivel 1
     */
    protected function hasValidLevel1Signature(PermissionRequest $permission): bool
    {
        // Solo necesita tener firma de nivel 1 (el jefe)
        // La firma del empleado es opcional ahora
        return $permission->digitalSignatures()
                   ->where('signature_type', 'level1_supervisor')
                   ->where('is_valid', true)
                   ->exists();
    }

    /**
     * Determinar tipo de firma según usuario actual
     */
    protected function determineSignatureType(PermissionRequest $permission): string
    {
        $user = auth()->user();
        
        // Si es el empleado dueño de la solicitud
        if ($user->id === $permission->user_id) {
            return 'employee';
        }
        
        // Si tiene rol de jefe inmediato
        if ($user->hasRole('jefe_inmediato')) {
            return 'level1_supervisor';
        }
        
        // Si tiene rol de RRHH
        if ($user->hasRole('jefe_rrhh')) {
            return 'level2_hr';
        }
        
        throw new \Exception('Usuario no autorizado para firmar esta solicitud');
    }

    /**
     * Generar nombre único para archivo firmado
     */
    protected function generateSignedFilename(PermissionRequest $permission, string $signatureType): string
    {
        $timestamp = now()->format('Y-m-d');
        $unique = uniqid();
        
        return "signed_{$permission->request_number}_{$signatureType}_{$timestamp}_{$unique}.pdf";
    }

    /**
     * Obtener etapa de firma
     */
    protected function getSignatureStage(string $signatureType): int
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
     * Actualizar estado de la solicitud después de firmar
     */
    protected function updatePermissionStatus(PermissionRequest $permission, string $signatureType): void
    {
        switch ($signatureType) {
            case 'employee':
                // Para empleados, NO cambiar el estado - seguirá en draft hasta que se envíe manualmente
                // La firma ya está registrada, el estado permanece en draft
                break;
                
            case 'level1_supervisor':
                // IMPORTANTE: La firma del jefe inmediato NO debe aprobar automáticamente
                // Solo registra la firma digital. La aprobación debe hacerse manualmente
                // después a través del botón "Aprobar" en la interfaz
                break;
                
            case 'level2_hr':
                // IMPORTANTE: La firma de RRHH NO debe aprobar automáticamente
                // Solo registra la firma digital. La aprobación debe hacerse manualmente
                // después a través del botón "Aprobar" en la interfaz
                break;
        }
    }

    /**
     * Obtener siguiente etapa
     */
    protected function getNextStage(string $signatureType): ?string
    {
        switch ($signatureType) {
            case 'employee':
                return 'submit_for_approval'; // Empleado debe enviar la solicitud manualmente
            case 'level1_supervisor':
                return 'manual_approval_required'; // Jefe debe aprobar manualmente después de firmar
            case 'level2_hr':
                return 'manual_approval_required'; // RRHH debe aprobar manualmente después de firmar
            default:
                return null;
        }
    }

    /**
     * Obtener el documento más reciente firmado
     */
    public function getLatestSignedDocument(PermissionRequest $permission): ?DigitalSignature
    {
        return $permission->digitalSignatures()
            ->where('is_valid', true)
            ->latest('signed_at')
            ->first();
    }

    /**
     * Validar integridad de todas las firmas
     */
    public function validateAllSignatures(PermissionRequest $permission): array
    {
        $signatures = $permission->digitalSignatures()->where('is_valid', true)->get();
        $results = [];
        
        foreach ($signatures as $signature) {
            $results[] = [
                'signature_id' => $signature->id,
                'signature_type' => $signature->signature_type,
                'signer' => $signature->user->name,
                'signed_at' => $signature->signed_at,
                'integrity_valid' => $signature->verifyDocumentIntegrity(),
                'document_exists' => $signature->document_exists
            ];
        }
        
        return $results;
    }

    /**
     * Obtener posición de firma según el tipo de usuario
     * Configuración para las 3 cajas horizontales del PDF template
     */
    protected function getSignaturePosition(string $signatureType): array
    {
        $positions = [
            'employee' => [
                'positionx' => 90,   // Caja izquierda - Empleado
                'positiony' => 430,
                'role' => 'Empleado'
            ],
            'level1_supervisor' => [
                'positionx' => 270,  // Caja centro - Jefe Inmediato
                'positiony' => 430,
                'role' => 'Jefe Inmediato'
            ],
            'level2_hr' => [
                'positionx' => 430,  // Caja derecha - RRHH
                'positiony' => 430,
                'role' => 'Escalafon RRHH'
            ]
        ];

        return $positions[$signatureType] ?? [
            'positionx' => 60,
            'positiony' => 650,
            'role' => 'Usuario'
        ];
    }

    /**
     * Decodificar payload de JWT sin verificación (solo para extraer datos)
     */
    protected function decodeJwtPayload(string $jwt): ?array
    {
        try {
            $parts = explode('.', $jwt);
            if (count($parts) !== 3) {
                return null;
            }

            // Decodificar el payload (segunda parte)
            $payload = $parts[1];
            
            // Agregar padding si es necesario
            $payload .= str_repeat('=', (4 - strlen($payload) % 4) % 4);
            
            $decodedPayload = base64_decode($payload, true);
            if ($decodedPayload === false) {
                return null;
            }

            return json_decode($decodedPayload, true);
            
        } catch (\Exception $e) {
            Log::warning('Error al decodificar JWT payload', ['error' => $e->getMessage()]);
            return null;
        }
    }
}