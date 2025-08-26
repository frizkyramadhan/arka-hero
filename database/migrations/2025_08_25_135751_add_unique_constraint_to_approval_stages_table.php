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
        Schema::table('approval_stages', function (Blueprint $table) {
            // Add unique constraint to prevent duplicate combinations
            $table->unique(
                ['project_id', 'department_id', 'document_type', 'approver_id', 'approval_order'],
                'unique_approval_stage_combination'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_stages', function (Blueprint $table) {
            // Remove unique constraint
            $table->dropUnique('unique_approval_stage_combination');
        });
    }
};
