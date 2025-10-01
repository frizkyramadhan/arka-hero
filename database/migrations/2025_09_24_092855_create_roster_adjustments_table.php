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
        Schema::create('roster_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roster_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_request_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('adjustment_type', ['+days', '-days']); // +days / -days
            $table->integer('adjusted_value'); // Nilai penyesuaian
            $table->string('reason'); // Alasan adjustment (mis. cuti)
            $table->timestamps();

            // Index for better performance
            $table->index(['roster_id', 'adjustment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_adjustments');
    }
};
