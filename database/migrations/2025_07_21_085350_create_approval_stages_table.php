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
        Schema::create('approval_stages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('approval_flow_id')->comment('Reference to approval flow');
            $table->string('stage_name', 255)->comment('Name of the approval stage');
            $table->integer('stage_order')->comment('Order of this stage in the flow');
            $table->enum('stage_type', ['sequential', 'parallel'])->default('sequential')->comment('Type of stage execution');
            $table->boolean('is_mandatory')->default(true)->comment('Whether this stage is mandatory');
            $table->json('auto_approve_conditions')->nullable()->comment('Conditions for auto-approval');
            $table->integer('escalation_hours')->default(72)->comment('Hours before escalation');
            $table->timestamps();

            // Indexes
            $table->index(['approval_flow_id', 'stage_order'], 'idx_flow_order');
            $table->index('stage_type', 'idx_stage_type');
            $table->index('is_mandatory', 'idx_is_mandatory');

            // Foreign keys
            $table->foreign('approval_flow_id')->references('id')->on('approval_flows')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_stages');
    }
};
