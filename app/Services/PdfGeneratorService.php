<?php

namespace App\Services;

use App\Models\PermissionRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class PdfGeneratorService
{
    /**
     * Generar PDF de solicitud de permiso para firmar
     */
    public function generatePermissionRequestPdf(PermissionRequest $permissionRequest)
    {
        try {
            // Cargar relaciones necesarias
            $permissionRequest->load(['user.department', 'user.immediateSupervisor', 'permissionType', 'documents']);
            
            // Generar el PDF usando la vista
            $pdf = Pdf::loadView('permissions.pdf-template', [
                'request' => $permissionRequest,
                'for_signature' => true // Indica que es para firma
            ]);
            
            // Configurar opciones del PDF
            $pdf->setPaper('A4', 'portrait');
            $pdf->setOptions([
                'defaultFont' => 'DejaVu Sans',
                'isPhpEnabled' => true,
                'isRemoteEnabled' => true
            ]);
            
            return [
                'success' => true,
                'pdf' => $pdf,
                'filename' => $this->generateFilename($permissionRequest)
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al generar PDF: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Guardar PDF firmado subido por el usuario
     */
    public function saveSignedPdf(PermissionRequest $permissionRequest, $uploadedFile)
    {
        try {
            // Validar que es un PDF
            if ($uploadedFile->getMimeType() !== 'application/pdf') {
                throw new \Exception('El archivo debe ser un PDF');
            }
            
            // Validar tamaño (máximo 10MB)
            if ($uploadedFile->getSize() > 10485760) {
                throw new \Exception('El archivo es demasiado grande. Máximo 10MB');
            }
            
            // Generar nombre y ruta del archivo
            $filename = 'signed_' . $permissionRequest->request_number . '_' . time() . '.pdf';
            $path = 'signed-documents/' . date('Y/m') . '/' . $filename;
            
            // Guardar archivo
            $uploadedFile->storeAs('signed-documents/' . date('Y/m'), $filename);
            
            // Calcular hash del archivo para integridad
            $fullPath = Storage::path($path);
            $fileHash = hash_file('sha256', $fullPath);
            
            return [
                'success' => true,
                'path' => $path,
                'filename' => $filename,
                'size' => $uploadedFile->getSize(),
                'hash' => $fileHash
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Validar PDF firmado básicamente
     */
    public function validateSignedPdf($filePath)
    {
        try {
            // Verificar que el archivo existe
            if (!Storage::exists($filePath)) {
                return [
                    'success' => false,
                    'message' => 'Archivo no encontrado'
                ];
            }
            
            $fullPath = Storage::path($filePath);
            
            // Validaciones básicas
            $validations = [
                'file_exists' => file_exists($fullPath),
                'is_readable' => is_readable($fullPath),
                'file_size' => filesize($fullPath),
                'mime_type' => mime_content_type($fullPath),
                'pdf_header' => $this->validatePdfHeader($fullPath),
                'has_signature' => $this->checkForDigitalSignature($fullPath)
            ];
            
            // Verificar que es un PDF válido
            if ($validations['mime_type'] !== 'application/pdf' || !$validations['pdf_header']) {
                return [
                    'success' => false,
                    'message' => 'El archivo no es un PDF válido'
                ];
            }
            
            // Verificar que tiene firma digital (búsqueda básica)
            if (!$validations['has_signature']) {
                return [
                    'success' => false,
                    'message' => 'El PDF no parece tener firma digital',
                    'warning' => true // Es una advertencia, no un error crítico
                ];
            }
            
            return [
                'success' => true,
                'validations' => $validations,
                'message' => 'PDF válido con firma digital'
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al validar PDF: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Generar nombre de archivo para la solicitud
     */
    protected function generateFilename(PermissionRequest $permissionRequest)
    {
        return sprintf(
            'solicitud_%s_%s_%s.pdf',
            $permissionRequest->request_number,
            Str::slug($permissionRequest->user->first_name . '_' . $permissionRequest->user->last_name),
            date('Y-m-d')
        );
    }
    
    /**
     * Validar header de PDF
     */
    protected function validatePdfHeader($filePath)
    {
        $handle = fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }
        
        $header = fread($handle, 5);
        fclose($handle);
        
        return strpos($header, '%PDF-') === 0;
    }
    
    /**
     * Buscar indicios de firma digital en el PDF
     */
    protected function checkForDigitalSignature($filePath)
    {
        try {
            // Leer una porción del archivo para buscar indicadores de firma
            $content = file_get_contents($filePath, false, null, 0, 50000); // Primeros 50KB
            
            // Buscar palabras clave que indican firma digital
            $signatureIndicators = [
                '/Sig ',
                '/Type/Sig',
                '/ByteRange',
                '/Contents',
                'Adobe.PPKLite',
                'ETSI.CAdES.detached',
                '/SubFilter',
                'adbe.pkcs7.detached',
                'Certificate'
            ];
            
            foreach ($signatureIndicators as $indicator) {
                if (strpos($content, $indicator) !== false) {
                    return true;
                }
            }
            
            return false;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Obtener información básica del PDF firmado
     */
    public function getSignedPdfInfo($filePath)
    {
        try {
            if (!Storage::exists($filePath)) {
                return null;
            }
            
            $fullPath = Storage::path($filePath);
            
            return [
                'file_size' => filesize($fullPath),
                'file_hash' => hash_file('sha256', $fullPath),
                'mime_type' => mime_content_type($fullPath),
                'created_at' => date('Y-m-d H:i:s', filemtime($fullPath)),
                'readable' => is_readable($fullPath)
            ];
            
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Limpiar archivos temporales antiguos
     */
    public function cleanupOldFiles($daysOld = 30)
    {
        try {
            $cutoffDate = now()->subDays($daysOld);
            
            // Limpiar directorio temporal si existe
            $tempFiles = Storage::files('temp/pdf-generation');
            $deletedCount = 0;
            
            foreach ($tempFiles as $file) {
                $lastModified = Storage::lastModified($file);
                if ($lastModified < $cutoffDate->timestamp) {
                    Storage::delete($file);
                    $deletedCount++;
                }
            }
            
            return [
                'success' => true,
                'deleted_files' => $deletedCount
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}