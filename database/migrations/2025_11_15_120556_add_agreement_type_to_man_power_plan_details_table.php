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
        Schema::table('man_power_plan_details', function (Blueprint $table) {
            $table->string('agreement_type', 20)->default('pkwt')->after('requires_theory_test')
                ->comment('Agreement type: pkwt, pkwtt, magang, harian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('man_power_plan_details', function (Blueprint $table) {
            $table->dropColumn('agreement_type');
        });
    }
};
