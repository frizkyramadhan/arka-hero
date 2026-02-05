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
        Schema::create('flight_request_issuance', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Foreign Keys (Many-to-Many)
            $table->uuid('flight_request_id');
            $table->uuid('flight_request_issuance_id');

            // Timestamps
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('flight_request_id')
                ->references('id')
                ->on('flight_requests')
                ->onDelete('cascade');

            $table->foreign('flight_request_issuance_id')
                ->references('id')
                ->on('flight_request_issuances')
                ->onDelete('cascade');

            // Unique Constraint (prevent duplicate)
            $table->unique(['flight_request_id', 'flight_request_issuance_id'], 'fr_issuance_unique');

            // Indexes
            $table->index('flight_request_id');
            $table->index('flight_request_issuance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_request_issuance');
    }
};
