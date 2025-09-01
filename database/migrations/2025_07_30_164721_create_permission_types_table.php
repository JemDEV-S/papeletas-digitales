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
        Schema::create('permission_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->integer('max_hours_per_day')->nullable();
            $table->integer('max_hours_per_month')->nullable();
            $table->integer('max_times_per_month')->nullable();
            $table->boolean('requires_document')->default(false);
            $table->boolean('with_pay')->default(true); // Con o sin goce de remuneraciones
            $table->json('validation_rules')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permission_types');
    }
};