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
        Schema::table('approval_stage_details', function (Blueprint $table) {
            // Add request_reason field for conditional approval (recruitment_request only)
            $table->string('request_reason', 50)->nullable()->after('department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_stage_details', function (Blueprint $table) {
            // Remove request_reason field
            $table->dropColumn('request_reason');
        });
    }
};
