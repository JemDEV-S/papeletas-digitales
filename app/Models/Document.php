<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'permission_request_id',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'file_size',
        'document_type',
        'file_hash',
    ];

    /**
     * Document type constants
     */
    const TYPE_CERTIFICADO_MEDICO = 'certificado_medico';
    const TYPE_CITACION = 'citacion';
    const TYPE_ACREDITACION = 'acreditacion';
    const TYPE_RESOLUCION_NOMBRAMIENTO = 'resolucion_nombramiento';
    const TYPE_HORARIO_ENSENANZA = 'horario_ensenanza';
    const TYPE_HORARIO_RECUPERACION = 'horario_recuperacion';
    const TYPE_PARTIDA_NACIMIENTO = 'partida_nacimiento';
    const TYPE_DECLARACION_JURADA = 'declaracion_jurada';
    const TYPE_OTROS = 'otros';

    /**
     * Relationships
     */
    public function permissionRequest()
    {
        return $this->belongsTo(PermissionRequest::class);
    }

    /**
     * Get the full URL to the document
     */
    public function getUrlAttribute(): string
    {
        return route('permissions.documents.view', [
            'permission' => $this->permission_request_id,
            'document' => $this->id
        ]);
    }

    /**
     * Get human readable file size
     */
    public function getHumanFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get document type label
     */
    public function getDocumentTypeLabel(): string
    {
        return match($this->document_type) {
            self::TYPE_CERTIFICADO_MEDICO => 'Certificado Médico',
            self::TYPE_CITACION => 'Citación',
            self::TYPE_ACREDITACION => 'Acreditación',
            self::TYPE_RESOLUCION_NOMBRAMIENTO => 'Resolución de Nombramiento',
            self::TYPE_HORARIO_ENSENANZA => 'Horario de Enseñanza',
            self::TYPE_HORARIO_RECUPERACION => 'Horario de Recuperación',
            self::TYPE_PARTIDA_NACIMIENTO => 'Partida de Nacimiento',
            self::TYPE_DECLARACION_JURADA => 'Declaración Jurada',
            self::TYPE_OTROS => 'Otros',
            default => 'Documento',
        };
    }

    /**
     * Check if document is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if document is a PDF
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Delete the file from storage when model is deleted
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($document) {
            Storage::delete($document->file_path);
        });
    }

    /**
     * Verify file integrity
     */
    public function verifyIntegrity(): bool
    {
        if (!Storage::exists($this->file_path)) {
            return false;
        }

        $currentHash = hash_file('sha256', Storage::path($this->file_path));
        return $currentHash === $this->file_hash;
    }

    /**
     * Get available document types
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_CERTIFICADO_MEDICO => 'Certificado Médico',
            self::TYPE_CITACION => 'Citación',
            self::TYPE_ACREDITACION => 'Acreditación',
            self::TYPE_RESOLUCION_NOMBRAMIENTO => 'Resolución de Nombramiento',
            self::TYPE_HORARIO_ENSENANZA => 'Horario de Enseñanza',
            self::TYPE_HORARIO_RECUPERACION => 'Horario de Recuperación',
            self::TYPE_PARTIDA_NACIMIENTO => 'Partida de Nacimiento',
            self::TYPE_DECLARACION_JURADA => 'Declaración Jurada',
            self::TYPE_OTROS => 'Otros',
        ];
    }
}