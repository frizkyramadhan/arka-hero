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
        Schema::create('rosters', function (Blueprint $table) {
            $table->id();
            $table->char('employee_id', 36);
            $table->bigInteger('administration_id')->unsigned();
            $table->foreignId('roster_template_id')->constrained()->onDelete('cascade');
            $table->date('start_date'); // Mulai siklus
            $table->date('end_date'); // Akhir siklus
            $table->integer('cycle_no'); // Nomor siklus
            $table->integer('adjusted_days')->default(0); // Penyesuaian karena cuti
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('administration_id')->references('id')->on('administrations')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['employee_id', 'is_active']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rosters');
    }
};
