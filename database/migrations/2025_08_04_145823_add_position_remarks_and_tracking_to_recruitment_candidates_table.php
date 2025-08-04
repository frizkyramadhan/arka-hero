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
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            // Add position_applied and remarks columns
            $table->string('position_applied', 255)->nullable()->after('experience_years');
            $table->text('remarks')->nullable()->after('position_applied');

            // Add tracking columns
            $table->unsignedBigInteger('created_by')->nullable()->after('global_status');
            $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');

            // Add blacklist tracking columns
            $table->text('blacklist_reason')->nullable()->after('updated_by');
            $table->timestamp('blacklisted_at')->nullable()->after('blacklist_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recruitment_candidates', function (Blueprint $table) {
            $table->dropColumn(['position_applied', 'remarks', 'created_by', 'updated_by', 'blacklist_reason', 'blacklisted_at']);
        });
    }
};
