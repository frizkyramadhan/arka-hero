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
            // Drop foreign key constraint first
            $table->dropForeign(['project_id']);

            // Drop columns that will move to details table
            $table->dropColumn(['project_id', 'department_id']);

            // Update unique constraint to new structure
            $table->dropUnique('unique_approval_stage_combination');
            $table->unique(['document_type', 'approver_id', 'approval_order'], 'unique_approval_stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_stages', function (Blueprint $table) {
            // Add back the dropped columns
            $table->foreignId('project_id')->after('document_type')->constrained('projects');
            $table->string('department_id', 255)->after('project_id');

            // Restore original unique constraint
            $table->dropUnique('unique_approval_stage');
            $table->unique(
                ['project_id', 'department_id', 'document_type', 'approver_id', 'approval_order'],
                'unique_approval_stage_combination'
            );
        });
    }
};
