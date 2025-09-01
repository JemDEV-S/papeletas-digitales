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
        Schema::table('users', function (Blueprint $table) {
            $table->string('dni', 8)->unique()->after('id');
            $table->string('first_name')->after('dni');
            $table->string('last_name')->after('first_name');
            $table->unsignedBigInteger('department_id')->nullable()->after('password');
            $table->unsignedBigInteger('role_id')->after('department_id');
            $table->unsignedBigInteger('immediate_supervisor_id')->nullable()->after('role_id');
            $table->boolean('is_active')->default(true)->after('immediate_supervisor_id');
            $table->datetime('two_factor_expires_at')->nullable();
            $table->string('two_factor_secret')->nullable();
            
            // Cambiar el campo name por nullable ya que usaremos first_name y last_name
            $table->string('name')->nullable()->change();
            
            // Foreign keys
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('immediate_supervisor_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar foreign keys primero
            $table->dropForeign(['department_id']);
            $table->dropForeign(['role_id']);
            $table->dropForeign(['immediate_supervisor_id']);
            
            // Eliminar columnas
            $table->dropColumn([
                'dni',
                'first_name',
                'last_name',
                'department_id',
                'role_id',
                'immediate_supervisor_id',
                'is_active',
                'two_factor_expires_at',
                'two_factor_secret'
            ]);
        });
    }
};