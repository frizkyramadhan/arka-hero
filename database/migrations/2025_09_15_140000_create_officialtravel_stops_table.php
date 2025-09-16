<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('officialtravel_stops', function (Blueprint $table) {
            $table->id();
            $table->uuid('official_travel_id');
            $table->foreign('official_travel_id')->references('id')->on('officialtravels')->onDelete('cascade');

            // Arrival fields
            $table->datetime('arrival_at_destination')->nullable();
            $table->foreignId('arrival_check_by')->nullable()->constrained('users');
            $table->text('arrival_remark')->nullable();
            $table->timestamp('arrival_timestamps')->nullable();

            // Departure fields
            $table->datetime('departure_from_destination')->nullable();
            $table->foreignId('departure_check_by')->nullable()->constrained('users');
            $table->text('departure_remark')->nullable();
            $table->timestamp('departure_timestamps')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('official_travel_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('officialtravel_stops');
    }
};
