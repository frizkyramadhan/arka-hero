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
        Schema::create('roster_daily_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->enum('status_code', ['D', 'N', 'OFF', 'S', 'I', 'A', 'C'])->default('D');
            $table->text('notes')->nullable();
            $table->timestamps();

            // Unique constraint untuk mencegah duplicate status per hari
            $table->unique(['roster_id', 'date'], 'idx_roster_date');

            // Index untuk performance
            $table->index(['date', 'status_code'], 'idx_date_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_daily_status');
    }
};
