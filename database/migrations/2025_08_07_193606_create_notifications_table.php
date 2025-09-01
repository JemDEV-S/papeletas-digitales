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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique(); // UUID para identificación única
            
            // Información del destinatario
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Información del remitente (puede ser null para notificaciones del sistema)
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            
            // Tipo y categoría de notificación
            $table->string('type'); // permission_submitted, permission_approved, etc.
            $table->string('category')->default('permission'); // permission, system, admin, etc.
            $table->string('priority')->default('normal'); // low, normal, high, urgent
            
            // Contenido de la notificación
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable(); // Datos adicionales como permission_id, etc.
            
            // Relación con la solicitud de permiso (puede ser null para notificaciones generales)
            $table->foreignId('permission_request_id')->nullable()->constrained()->onDelete('cascade');
            
            // Estados y fechas
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at')->nullable(); // Cuando se envió por email
            $table->timestamp('delivered_at')->nullable(); // Cuando se entregó
            $table->boolean('is_broadcast')->default(false); // Si fue enviada por broadcasting
            $table->boolean('is_email_sent')->default(false); // Si se envió por email
            
            // Configuración de expiración
            $table->timestamp('expires_at')->nullable();
            
            // Metadatos
            $table->string('channel')->nullable(); // email, sms, push, database
            $table->string('reference_type')->nullable(); // Tipo de entidad relacionada
            $table->unsignedBigInteger('reference_id')->nullable(); // ID de la entidad relacionada
            $table->json('metadata')->nullable(); // Metadatos adicionales
            
            $table->timestamps();
            
            // Índices para optimizar consultas
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'category']);
            $table->index(['user_id', 'created_at']);
            $table->index(['permission_request_id']);
            $table->index(['type', 'created_at']);
            $table->index(['expires_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
