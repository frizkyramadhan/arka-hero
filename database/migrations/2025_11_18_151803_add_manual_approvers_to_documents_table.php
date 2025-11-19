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
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->json('manual_approvers')->nullable()->after('status');
        });

        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->json('manual_approvers')->nullable()->after('status');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->json('manual_approvers')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('officialtravels', function (Blueprint $table) {
            $table->dropColumn('manual_approvers');
        });

        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->dropColumn('manual_approvers');
        });

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('manual_approvers');
        });
    }
};
