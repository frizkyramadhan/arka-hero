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
        Schema::create('flight_request_issuance_details', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Reference
            $table->uuid('flight_request_issuance_id');

            // Ticket Detail Info
            $table->integer('ticket_order'); // 1, 2, 3, dst (untuk ordering)
            $table->string('booking_code', 50)->nullable(); // KODE BOOKING: J7G2JI
            $table->text('detail_reservation')->nullable(); // Detail reservasi (12 JAN 2026 // SUB TRK // 05.10)
            $table->string('passenger_name'); // NAMA PENUMPANG: DWI NURTIKTO
            $table->decimal('ticket_price', 15, 2)->nullable(); // TICKET PRICE: 1.200.700

            // Service & cost allocation
            $table->decimal('service_charge', 15, 2)->nullable();
            $table->decimal('service_vat', 15, 2)->nullable();
            $table->decimal('company_amount', 15, 2)->nullable(); // nominal ditanggung perusahaan
            $table->decimal('advance_amount', 15, 2)->nullable(); // nominal ditanggung karyawan

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('flight_request_issuance_id', 'fk_issuance_details_issuance')
                ->references('id')
                ->on('flight_request_issuances')
                ->onDelete('cascade');

            // Indexes
            $table->index('flight_request_issuance_id', 'idx_issuance_details_issuance');
            $table->index('ticket_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_request_issuance_details');
    }
};
