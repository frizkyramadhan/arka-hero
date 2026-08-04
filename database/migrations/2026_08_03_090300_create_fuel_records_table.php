<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('vehicle_id');
            $table->date('fuel_date');
            $table->unsignedInteger('odometer');
            $table->string('fuel_type', 50);
            $table->decimal('quantity', 10, 2);
            $table->decimal('price_per_liter', 10, 2);
            $table->decimal('total_cost', 15, 2);
            $table->string('fuel_station')->nullable();
            $table->uuid('driver_id')->nullable();
            $table->string('receipt_number', 100)->nullable();
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('vehicle_id')->references('id')->on('vehicles')->cascadeOnDelete();
            $table->index(['vehicle_id', 'fuel_date']);
            $table->index('fuel_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_records');
    }
};
