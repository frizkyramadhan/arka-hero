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
            $table->integer('approval_order')->default(1)->after('document_type')->comment('Order of approval step');
            $table->boolean('is_sequential')->default(true)->after('approval_order')->comment('Whether approval must be sequential');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_stages', function (Blueprint $table) {
            $table->dropColumn(['approval_order', 'is_sequential']);
        });
    }
};
