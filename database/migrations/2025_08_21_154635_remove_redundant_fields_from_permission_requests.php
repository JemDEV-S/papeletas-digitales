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
            $table->dropColumn(['start_datetime', 'end_datetime', 'requested_hours']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permission_requests', function (Blueprint $table) {
            $table->datetime('start_datetime')->after('permission_type_id');
            $table->datetime('end_datetime')->after('start_datetime');
            $table->decimal('requested_hours', 5, 2)->after('end_datetime');
        });
    }
};
