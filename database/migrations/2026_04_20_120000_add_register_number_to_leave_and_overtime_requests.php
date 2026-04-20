<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Simple yearly register numbers: e.g. 26LV-00001, 26OT-00001 (same idea as flight 26FRF-xxxxx).
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('register_number', 32)->nullable()->unique()->after('id');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->string('register_number', 32)->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropUnique(['register_number']);
            $table->dropColumn('register_number');
        });

        Schema::table('overtime_requests', function (Blueprint $table) {
            $table->dropUnique(['register_number']);
            $table->dropColumn('register_number');
        });
    }
};
