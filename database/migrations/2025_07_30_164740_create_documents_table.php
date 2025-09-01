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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_request_id');
            $table->string('original_name');
            $table->string('stored_name');
            $table->string('file_path');
            $table->string('mime_type');
            $table->integer('file_size');
            $table->string('document_type'); // certificado_medico, citacion, etc.
            $table->string('file_hash'); // Para verificar integridad
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('permission_request_id')->references('id')->on('permission_requests');
            
            // Indexes
            $table->index('permission_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};