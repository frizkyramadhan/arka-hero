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
        Schema::table('levels', function (Blueprint $table) {
            $table->integer('off_days')->default(14)->after('level_order');
            $table->integer('work_days')->nullable()->after('off_days');
            $table->integer('cycle_length')->nullable()->after('work_days');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('levels', function (Blueprint $table) {
            $table->dropColumn(['off_days', 'work_days', 'cycle_length']);
        });
    }
};
