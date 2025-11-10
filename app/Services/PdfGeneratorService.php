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
     * Generar página de tracking separada (sin modificar PDF original)
     * Esto preserva las firmas digitales del PDF original
     */
    public function addTrackingOverlay(PermissionRequest $permissionRequest)
    {
        try {
            // Cargar tracking
            if (!$permissionRequest->tracking || !$permissionRequest->tracking->return_datetime) {
                throw new \Exception('No hay datos de tracking completo para agregar al PDF');
            }

            $tracking = $permissionRequest->tracking;

            // Crear directorio si no existe
            $trackingDir = storage_path('app/tracking-pdfs');
            if (!file_exists($trackingDir)) {
                mkdir($trackingDir, 0755, true);
            }

            // Nombre del archivo con tracking (solo la página de tracking)
            $trackingFilename = sprintf(
                'tracking_%s_%s.pdf',
                $permissionRequest->request_number,
                date('YmdHis')
            );
            $trackingPdfPath = $trackingDir . '/' . $trackingFilename;

            // Crear PDF solo con la página de tracking
            $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

            // Configurar PDF
            $pdf->SetCreator('Sistema de Papeletas Digitales');
            $pdf->SetAuthor('Municipalidad Distrital de San Jerónimo');
            $pdf->SetTitle('Registro de Tracking - ' . $permissionRequest->request_number);
            $pdf->SetSubject('Registro de Salida y Regreso');

            // Quitar headers y footers
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);

            // Configurar márgenes
            $pdf->SetMargins(15, 15, 15);
            $pdf->SetAutoPageBreak(true, 15);

            // Agregar página
            $pdf->AddPage();

            // Configurar fuente
            $pdf->SetFont('helvetica', '', 10);

            // Dibujar sello de tracking
            $this->drawTrackingSeal($pdf, $permissionRequest, $tracking);

            // Guardar el PDF
            $pdf->Output($trackingPdfPath, 'F');

            // Guardar referencia en metadata del permiso
            $metadata = $permissionRequest->metadata ?? [];
            $metadata['tracking_pdf_path'] = 'tracking-pdfs/' . $trackingFilename;
            $metadata['tracking_pdf_generated_at'] = now()->toDateTimeString();
            $permissionRequest->update(['metadata' => $metadata]);

            \Log::info('Página de tracking generada (sin modificar PDF original)', [
                'permission_id' => $permissionRequest->id,
                'tracking_pdf' => $trackingFilename,
                'path' => $trackingPdfPath
            ]);

            return [
                'success' => true,
                'pdf_path' => 'tracking-pdfs/' . $trackingFilename,
                'filename' => $trackingFilename,
                'message' => 'Página de tracking generada exitosamente (PDF original preservado)'
            ];

        } catch (\Exception $e) {
            \Log::error('Error al generar página de tracking: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error al generar página de tracking: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Dibujar sello de tracking en el PDF
     */
    protected function drawTrackingSeal($pdf, $permissionRequest, $tracking)
    {
        // Configurar posición del sello (centrado)
        $x = 50;
        $y = 70;
        $width = 110;

        // Fondo del sello con borde
        $pdf->SetFillColor(240, 248, 255); // Azul claro
        $pdf->SetDrawColor(59, 130, 246); // Azul
        $pdf->SetLineWidth(0.5);
        $pdf->RoundedRect($x, $y, $width, 135, 3, '1111', 'DF');

        // Título del sello
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->SetTextColor(30, 58, 138); // Azul oscuro
        $pdf->SetXY($x, $y + 5);
        $pdf->Cell($width, 10, 'REGISTRO DE SALIDA Y REGRESO', 0, 1, 'C');

        // Línea separadora
        $pdf->SetDrawColor(59, 130, 246);
        $pdf->Line($x + 10, $y + 18, $x + $width - 10, $y + 18);

        // Número de permiso
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->SetTextColor(30, 58, 138); // Azul oscuro
        $pdf->SetXY($x + 10, $y + 23);
        $pdf->Cell($width - 20, 6, 'Permiso N° ' . $permissionRequest->request_number, 0, 1, 'C');

        // Línea separadora
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line($x + 10, $y + 31, $x + $width - 10, $y + 31);

        // Datos del empleado
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x + 10, $y + 35);
        $pdf->Cell($width - 20, 6, 'Empleado:', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetXY($x + 10, $y + 41);
        $pdf->Cell($width - 20, 5, $permissionRequest->user->full_name, 0, 1, 'L');

        $pdf->SetXY($x + 10, $y + 46);
        $pdf->Cell($width - 20, 5, 'DNI: ' . $permissionRequest->user->dni, 0, 1, 'L');

        // Salida
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(34, 197, 94); // Verde
        $pdf->SetXY($x + 10, $y + 58);
        $pdf->Cell(8, 6, chr(254), 0, 0, 'L'); // Checkmark
        $pdf->Cell($width - 28, 6, 'SALIDA REGISTRADA', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x + 15, $y + 65);
        $pdf->Cell($width - 25, 5, 'Fecha: ' . $tracking->departure_datetime->format('d/m/Y'), 0, 1, 'L');

        $pdf->SetXY($x + 15, $y + 70);
        $pdf->Cell($width - 25, 5, 'Hora: ' . $tracking->departure_datetime->format('H:i:s'), 0, 1, 'L');

        // Regreso
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(34, 197, 94); // Verde
        $pdf->SetXY($x + 10, $y + 82);
        $pdf->Cell(8, 6, chr(254), 0, 0, 'L'); // Checkmark
        $pdf->Cell($width - 28, 6, 'REGRESO REGISTRADO', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY($x + 15, $y + 89);
        $pdf->Cell($width - 25, 5, 'Fecha: ' . $tracking->return_datetime->format('d/m/Y'), 0, 1, 'L');

        $pdf->SetXY($x + 15, $y + 94);
        $pdf->Cell($width - 25, 5, 'Hora: ' . $tracking->return_datetime->format('H:i:s'), 0, 1, 'L');

        // Tiempo utilizado (convertir a horas:minutos)
        $totalMinutes = round($tracking->actual_hours_used * 60);
        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;
        $timeFormatted = sprintf('%d:%02d', $hours, $minutes);

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetTextColor(37, 99, 235); // Azul
        $pdf->SetXY($x + 10, $y + 106);
        $pdf->Cell($width - 20, 6, 'Tiempo utilizado: ' . $timeFormatted . ' horas', 0, 1, 'L');

        // Registrado por
        if ($tracking->registeredByUser) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetXY($x + 10, $y + 118);
            $pdf->Cell($width - 20, 5, 'Registrado por: ' . $tracking->registeredByUser->full_name, 0, 1, 'L');
        }

        // Notas si existen
        if ($tracking->notes) {
            $pdf->SetFont('helvetica', '', 8);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->SetXY($x + 10, $y + 124);
            $pdf->MultiCell($width - 20, 4, 'Notas: ' . substr($tracking->notes, 0, 100), 0, 'L');
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