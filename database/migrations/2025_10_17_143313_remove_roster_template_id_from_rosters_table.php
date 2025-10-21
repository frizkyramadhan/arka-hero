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
        Schema::table('rosters', function (Blueprint $table) {
            // Drop foreign key constraint first
            $table->dropForeign(['roster_template_id']);
            // Drop the column
            $table->dropColumn('roster_template_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rosters', function (Blueprint $table) {
            $table->foreignId('roster_template_id')->nullable()->constrained()->onDelete('cascade');
        });
    }
};
