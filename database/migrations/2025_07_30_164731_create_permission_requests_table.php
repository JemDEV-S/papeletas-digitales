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
        Schema::create('permission_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('permission_type_id');
            $table->datetime('start_datetime');
            $table->datetime('end_datetime');
            $table->decimal('requested_hours', 5, 2);
            $table->text('reason');
            $table->enum('status', [
                'draft',
                'pending_immediate_boss',
                'pending_hr',
                'approved',
                'rejected',
                'cancelled'
            ])->default('draft');
            $table->json('metadata')->nullable(); // Para datos adicionales según el tipo
            $table->datetime('submitted_at')->nullable();
            $table->integer('current_approval_level')->default(0);
            $table->timestamps();
            
            // Foreign keys
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('permission_type_id')->references('id')->on('permission_types');
            
            // Indexes
            $table->index(['user_id', 'status']);
            $table->index('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_requests');
    }
};