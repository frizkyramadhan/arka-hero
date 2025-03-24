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
        Schema::create('officialtravels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('official_travel_number');
            $table->date('official_travel_date');
            $table->foreignId('official_travel_origin')->nullable()->constrained('projects');
            $table->string('official_travel_status'); // draft, open, closed
            $table->foreignId('traveler_id')->constrained('administrations');
            $table->string('purpose');
            $table->string('destination');
            $table->string('duration');
            $table->date('departure_from');
            $table->foreignId('transportation_id')->constrained('transportations');
            $table->foreignId('accommodation_id')->constrained('accommodations');
            // arrival
            $table->date('arrival_at_destination')->nullable();
            $table->foreignId('arrival_check_by')->nullable()->constrained('users');
            $table->string('arrival_remark')->nullable();
            $table->timestamp('arrival_timestamps')->nullable();
            // departure
            $table->date('departure_at_destination')->nullable();
            $table->foreignId('departure_check_by')->nullable()->constrained('users');
            $table->string('departure_remark')->nullable();
            $table->timestamp('departure_timestamps')->nullable();
            // recommendation
            $table->enum('recommendation_status', ['pending', 'approved', 'rejected']);
            $table->string('recommendation_remark')->nullable();
            $table->foreignId('recommendation_by')->nullable()->constrained('users');
            $table->date('recommendation_date')->nullable();
            $table->timestamp('recommendation_timestamps')->nullable();
            // approval
            $table->enum('approval_status', ['pending', 'approved', 'rejected']);
            $table->string('approval_remark')->nullable();
            $table->foreignId('approval_by')->nullable()->constrained('users');
            $table->date('approval_date')->nullable();
            $table->timestamp('approval_timestamps')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('officialtravels');
    }
};
