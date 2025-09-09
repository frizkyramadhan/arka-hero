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
        Schema::table('recruitment_requests', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['known_by']);
            $table->dropForeign(['approved_by_pm']);
            $table->dropForeign(['approved_by_director']);

            // Remove legacy HR Acknowledgment fields
            $table->dropColumn([
                'known_by',
                'known_status',
                'known_at',
                'known_remark',
                'known_timestamps'
            ]);

            // Remove legacy Project Manager Approval fields
            $table->dropColumn([
                'approved_by_pm',
                'pm_approval_status',
                'pm_approved_at',
                'pm_approval_remark',
                'pm_approval_timestamps'
            ]);

            // Remove legacy Director Approval fields
            $table->dropColumn([
                'approved_by_director',
                'director_approval_status',
                'director_approved_at',
                'director_approval_remark',
                'director_approval_timestamps'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            // Restore legacy HR Acknowledgment fields
            $table->unsignedBigInteger('known_by')->nullable();
            $table->enum('known_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('known_at')->nullable();
            $table->text('known_remark')->nullable();
            $table->timestamp('known_timestamps')->nullable();

            // Restore legacy Project Manager Approval fields
            $table->unsignedBigInteger('approved_by_pm')->nullable();
            $table->enum('pm_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('pm_approved_at')->nullable();
            $table->text('pm_approval_remark')->nullable();
            $table->timestamp('pm_approval_timestamps')->nullable();

            // Restore legacy Director Approval fields
            $table->unsignedBigInteger('approved_by_director')->nullable();
            $table->enum('director_approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('director_approved_at')->nullable();
            $table->text('director_approval_remark')->nullable();
            $table->timestamp('director_approval_timestamps')->nullable();

            // Restore foreign key constraints
            $table->foreign('known_by')->references('id')->on('users');
            $table->foreign('approved_by_pm')->references('id')->on('users');
            $table->foreign('approved_by_director')->references('id')->on('users');
        });
    }
};
