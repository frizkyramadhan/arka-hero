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
        Schema::create('roster_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->integer('work_days'); // Jumlah hari kerja per siklus
            $table->integer('off_days_local'); // Jumlah off days untuk lokal
            $table->integer('off_days_nonlocal'); // Jumlah off days untuk non lokal
            $table->integer('cycle_length'); // Panjang siklus (hari)
            $table->date('effective_date'); // Tanggal berlaku
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Unique constraint for project, level, and effective_date combination
            $table->unique(['project_id', 'level_id', 'effective_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_templates');
    }
};
