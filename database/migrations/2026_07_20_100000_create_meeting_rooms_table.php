<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meeting_rooms', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('project_id');
            $table->string('room_name');
            $table->unsignedInteger('capacity')->nullable();
            $table->text('facilities')->nullable();
            $table->string('status', 20)->default('active'); // active, inactive, maintenance
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->cascadeOnDelete();
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('meeting_rooms');
    }
};
