<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_assignment_stops', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('assignment_id');
            $table->unsignedSmallInteger('sequence')->default(0);
            $table->string('stop_type', 20); // origin|destination|return
            $table->string('destination');
            $table->boolean('is_manual')->default(false);
            $table->time('depart_time')->nullable();
            $table->unsignedInteger('depart_km')->nullable();
            $table->time('arrive_time')->nullable();
            $table->unsignedInteger('arrive_km')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('assignment_id')->references('id')->on('vehicle_assignments')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['assignment_id', 'sequence']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_assignment_stops');
    }
};
