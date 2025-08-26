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
        Schema::table('approval_plans', function (Blueprint $table) {
            $table->integer('approval_order')->nullable()->after('is_read')->comment('Order of approval step');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_plans', function (Blueprint $table) {
            $table->dropColumn('approval_order');
        });
    }
};
