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
        Schema::create('flight_request_details', function (Blueprint $table) {
            // Primary Key
            $table->uuid('id')->primary();

            // Reference
            $table->uuid('flight_request_id');

            // Segment Info
            $table->integer('segment_order');
            $table->enum('segment_type', ['departure', 'return']);

            // Flight Request Info (User Input)
            $table->date('flight_date');
            $table->string('departure_city', 100);
            $table->string('arrival_city', 100);
            $table->string('airline', 100)->nullable(); // Maskapai pilihan
            $table->time('flight_time')->nullable();

            // Timestamps
            $table->timestamps();

            // Foreign Keys
            $table->foreign('flight_request_id')->references('id')->on('flight_requests')->onDelete('cascade');

            // Indexes
            $table->index('flight_request_id');
            $table->index('flight_date');
            $table->index('segment_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_request_details');
    }
};
