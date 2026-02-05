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
        Schema::create('flight_request_issuances', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Letter numbering integration fields
            $table->foreignId('letter_number_id')->nullable()->constrained('letter_numbers');
            $table->string('letter_number', 50)->nullable();

            // Issued Information (Letter of Guarantee - LG)
            $table->string('issued_number', 100)->unique(); // Format: FR002/Arka/LG/I/2026
            $table->date('issued_date');

            // Vendor/Business Partner
            $table->uuid('business_partner_id')->nullable(); // Vendor yang digunakan untuk booking

            // Issued By
            $table->unsignedBigInteger('issued_by'); // HCS Division Manager (FK to users.id)
            $table->timestamp('issued_at');

            // Approval fields (same as flight_requests)
            $table->json('manual_approvers')->nullable(); // JSON array of user IDs
            $table->timestamp('approved_at')->nullable();

            // Simple status: pending, approved, rejected
            $table->string('status', 20)->default('pending');

            // Additional Info
            $table->text('notes')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('business_partner_id')->references('id')->on('business_partners')->onDelete('set null');
            $table->foreign('issued_by')->references('id')->on('users')->onDelete('restrict');

            // Indexes
            $table->index('issued_number');
            $table->index('issued_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_request_issuances');
    }
};
