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
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_request_id');
            $table->unsignedBigInteger('approver_id');
            $table->integer('approval_level'); // 1: Jefe Inmediato, 2: Jefe RRHH
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->text('comments')->nullable();
            $table->datetime('approved_at')->nullable();
            $table->string('digital_signature_hash')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('permission_request_id')->references('id')->on('permission_requests');
            $table->foreign('approver_id')->references('id')->on('users');
            
            // Indexes
            $table->index(['permission_request_id', 'approval_level']);
            $table->index(['approver_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};