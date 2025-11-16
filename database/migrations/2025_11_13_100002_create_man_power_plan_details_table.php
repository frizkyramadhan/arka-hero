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
        Schema::create('man_power_plan_details', function (Blueprint $table) {
            $table->uuid('id')->primary(); // UUID primary key
            $table->uuid('mpp_id');

            // Position Information - using position_id directly (no jabatan, no level_id)
            $table->unsignedBigInteger('position_id')->comment('Link to positions table');

            // Quantity Unit
            $table->integer('qty_unit')->default(0)->comment('Quantity unit');

            // Existing Quantities (Manual Input)
            $table->integer('existing_qty_s')->default(0)->comment('Existing Staff quantity');
            $table->integer('existing_qty_ns')->default(0)->comment('Existing Non-Staff quantity');

            // Plan Quantities (Target/Required)
            $table->integer('plan_qty_s')->default(0)->comment('Planned Staff quantity');
            $table->integer('plan_qty_ns')->default(0)->comment('Planned Non-Staff quantity');

            // Fulfillment Tracking
            $table->timestamp('fulfilled_at')->nullable()->comment('When this position requirement was fulfilled');

            // Additional Info
            $table->text('remarks')->nullable();

            // Theory Test Requirement
            $table->boolean('requires_theory_test')->default(false)->comment('Whether this position requires theory test');

            $table->timestamps();

            // Foreign Keys
            $table->foreign('mpp_id')->references('id')->on('man_power_plans')->onDelete('cascade');
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('restrict');

            // Indexes
            $table->index('mpp_id');
            $table->index('position_id');
            $table->index('fulfilled_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('man_power_plan_details');
    }
};
