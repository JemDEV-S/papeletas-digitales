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
        Schema::create('permission_trackings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('permission_request_id');
            $table->string('employee_dni', 8);
            $table->datetime('departure_datetime')->nullable();
            $table->datetime('return_datetime')->nullable();
            $table->decimal('actual_hours_used', 5, 2)->nullable();
            $table->enum('tracking_status', [
                'pending',      // Aprobado pero aún no ha salido
                'out',          // Ha salido, esperando regreso
                'returned',     // Ha regresado
                'overdue'       // No regresó en tiempo esperado
            ])->default('pending');
            $table->unsignedBigInteger('registered_by_user_id')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('permission_request_id')->references('id')->on('permission_requests')->onDelete('cascade');
            $table->foreign('registered_by_user_id')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index(['employee_dni', 'tracking_status']);
            $table->index('departure_datetime');
            $table->index('return_datetime');
            $table->unique('permission_request_id'); // Una solicitud = un tracking
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_trackings');
    }
};
