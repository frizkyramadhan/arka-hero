<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Drop unused/redundant columns from leave_entitlements table:
     * - withdrawable_days: Always same as entitled_days (redundant)
     * - remaining_days: Calculated field (now accessor in model)
     * - carried_over: Never used (0% usage)
     */
    public function up()
    {
        Schema::table('leave_entitlements', function (Blueprint $table) {
            $table->dropColumn(['withdrawable_days', 'remaining_days', 'carried_over']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('leave_entitlements', function (Blueprint $table) {
            $table->integer('withdrawable_days')->default(0)->after('entitled_days');
            $table->integer('remaining_days')->default(0)->after('taken_days');
            $table->integer('carried_over')->default(0)->after('deposit_days');
        });
    }
};
