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
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending_immediate_boss',
                'pending_hr',
                'approved',
                'in_progress',
                'rejected',
                'cancelled'
            ])->default('draft')->after('reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->dropColumn('status');
        });
        
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->enum('status', [
                'draft',
                'pending_immediate_boss',
                'pending_hr',
                'approved',
                'rejected',
                'cancelled'
            ])->default('draft')->after('reason');
        });
    }
};
