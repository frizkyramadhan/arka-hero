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
        Schema::create('roster_details', function (Blueprint $table) {
            $table->id();
            $table->uuid('roster_id');
            $table->foreign('roster_id')->references('id')->on('rosters')->onDelete('cascade');
            $table->integer('cycle_no')->comment('Nomor cycle rotasi (1, 2, 3, dst)');

            // Work Period
            $table->date('work_start')->comment('Tanggal mulai periode kerja');
            $table->date('work_end')->comment('Tanggal selesai periode kerja');
            $table->integer('adjusted_days')->default(0)->comment('Penyesuaian hari kerja (balancing)');

            // Leave Period
            $table->date('leave_start')->nullable()->comment('Tanggal mulai cuti periodik');
            $table->date('leave_end')->nullable()->comment('Tanggal selesai cuti periodik');

            // Status
            $table->enum('status', ['scheduled', 'active', 'on_leave', 'completed'])
                ->default('scheduled')
                ->comment('Status cycle: scheduled, active, on_leave, completed');

            // Additional Info
            $table->text('remarks')->nullable()->comment('Catatan atau keterangan');
            $table->timestamps();

            // Indexes for better performance
            $table->index(['roster_id', 'cycle_no']);
            $table->index(['work_start', 'work_end']);
            $table->index(['leave_start', 'leave_end']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roster_details');
    }
};
