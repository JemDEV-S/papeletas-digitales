<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('digital_signatures', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('permission_request_id')
                  ->constrained('permission_requests')
                  ->onDelete('cascade');
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');
            
            // Información básica de la firma
            $table->string('signature_type'); // onpe_employee_signature, onpe_supervisor_signature, etc.
            $table->string('certificate_serial')->nullable(); // Número de serie del certificado
            $table->string('signature_hash'); // SHA256 del documento firmado
            $table->string('signature_algorithm')->nullable(); // Algoritmo usado (SHA256withRSA, etc.)
            $table->string('signature_timestamp')->nullable(); // Timestamp de la firma
            $table->text('signer_dn')->nullable(); // Distinguished Name del firmante
            
            // Fechas
            $table->timestamp('signed_at'); // Fecha/hora de la firma
            
            // Datos del certificado y metadatos (JSON)
            $table->json('certificate_data')->nullable(); // Información del certificado
            $table->json('signature_metadata')->nullable(); // Metadatos adicionales
            
            // Ruta del documento firmado
            $table->string('document_path')->nullable(); // Ruta del PDF firmado
            
            // Estado de la firma
            $table->boolean('is_valid')->default(true); // Si la firma es válida
            
            // Timestamps automáticos
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['permission_request_id', 'signature_type']);
            $table->index(['user_id', 'signed_at']);
            $table->index(['signature_type', 'is_valid']);
            $table->index('certificate_serial');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('digital_signatures');
    }
};