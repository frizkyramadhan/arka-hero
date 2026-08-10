<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Convert existing installs that still have employee_id → user_id.
     * No-op on fresh installs that already created user_id columns.
     */
    public function up(): void
    {
        if (Schema::hasColumn('fuel_bot_subscribers', 'employee_id')) {
            Schema::table('fuel_bot_subscribers', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }

        if (! Schema::hasColumn('fuel_bot_subscribers', 'user_id')) {
            Schema::table('fuel_bot_subscribers', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->after('id');
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->index('user_id');
            });
        }

        if (Schema::hasColumn('fuel_bot_submissions', 'employee_id')) {
            Schema::table('fuel_bot_submissions', function (Blueprint $table) {
                $table->dropForeign(['employee_id']);
                $table->dropColumn('employee_id');
            });
        }

        if (! Schema::hasColumn('fuel_bot_submissions', 'user_id')) {
            Schema::table('fuel_bot_submissions', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('chat_id');
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fuel_bot_subscribers', 'user_id') && ! Schema::hasColumn('fuel_bot_subscribers', 'employee_id')) {
            Schema::table('fuel_bot_subscribers', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
                $table->uuid('employee_id')->after('id');
                $table->foreign('employee_id')->references('id')->on('employees')->cascadeOnDelete();
            });
        }

        if (Schema::hasColumn('fuel_bot_submissions', 'user_id') && ! Schema::hasColumn('fuel_bot_submissions', 'employee_id')) {
            Schema::table('fuel_bot_submissions', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
                $table->uuid('employee_id')->nullable()->after('chat_id');
                $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            });
        }
    }
};
