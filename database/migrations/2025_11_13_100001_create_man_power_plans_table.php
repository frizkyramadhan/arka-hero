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
        Schema::create('man_power_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('mpp_number', 50)->unique()->comment('MPP/YYYY/MM/SEQUENCE');
            
            // Project Information
            $table->unsignedBigInteger('project_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            
            // Status Management
            $table->enum('status', ['active', 'closed'])->default('active');
            
            // Audit Fields
            $table->unsignedBigInteger('created_by');
            $table->timestamps();
            
            // Foreign Keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            
            // Indexes
            $table->index('project_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('man_power_plans');
    }
};

