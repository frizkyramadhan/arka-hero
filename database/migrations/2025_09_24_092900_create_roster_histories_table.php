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
        Schema::create('roster_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_id')->constrained()->onDelete('cascade');
            $table->integer('cycle_no'); // Nomor siklus
            $table->integer('work_days_actual'); // Realisasi hari kerja
            $table->integer('off_days_actual'); // Realisasi hari off
            $table->text('remarks')->nullable(); // Catatan
            $table->timestamps();

            // Index for better performance
            $table->index(['roster_id', 'cycle_no']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_histories');
    }
};
