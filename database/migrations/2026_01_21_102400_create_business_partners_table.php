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
        Schema::create('business_partners', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Business Partner Info
            $table->string('bp_code', 50)->unique(); // e.g., BP-2026-001
            $table->string('bp_name');
            $table->text('bp_address')->nullable();
            $table->string('bp_phone', 20)->nullable();

            // Additional Info
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('bp_code');
            $table->index('bp_name');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_partners');
    }
};
