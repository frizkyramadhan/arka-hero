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
        Schema::create('approval_stage_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_stage_id')->constrained('approval_stages')->onDelete('cascade');
            $table->foreignId('project_id')->constrained('projects');
            $table->string('department_id', 255);
            $table->timestamps();

            $table->unique(['approval_stage_id', 'project_id', 'department_id'], 'unique_stage_detail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_stage_details');
    }
};
