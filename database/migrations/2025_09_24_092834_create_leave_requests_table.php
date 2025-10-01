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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->char('employee_id', 36);
            $table->bigInteger('administration_id')->unsigned();
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->date('start_date'); // Hari pertama cuti
            $table->date('end_date'); // Hari terakhir cuti
            $table->date('back_to_work_date')->nullable(); // Khusus LSL (tanggal kembali)
            $table->text('reason'); // Alasan cuti
            $table->integer('total_days'); // Lama cuti
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled', 'auto_approved'])->default('pending');
            $table->string('leave_period')->nullable(); // Untuk period cuti tahunan dan panjang
            $table->timestamp('requested_at')->nullable(); // Kapan diajukan
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('administration_id')->references('id')->on('administrations')->onDelete('cascade');

            // Indexes for better performance
            $table->index(['employee_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
