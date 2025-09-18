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
        Schema::create('bond_violations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_bond_id'); // Foreign Key ke tabel 'employee_bonds'
            $table->date('violation_date'); // Tanggal terjadinya pelanggaran
            $table->text('reason')->nullable(); // Alasan pelanggaran
            $table->integer('days_worked'); // Jumlah hari karyawan telah bekerja dalam masa ikatan dinas
            $table->integer('days_remaining'); // Jumlah hari sisa masa ikatan dinas
            $table->decimal('calculated_penalty_amount', 15, 2); // Jumlah penalty yang harus dibayar (hasil prorate)
            $table->decimal('penalty_paid_amount', 15, 2)->default(0); // Jumlah penalty yang sudah dibayar
            $table->date('payment_due_date')->nullable(); // Tanggal jatuh tempo pembayaran penalty (opsional)
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('employee_bond_id')->references('id')->on('employee_bonds')->onDelete('cascade');

            // Indexes
            $table->index('employee_bond_id');
            $table->index('violation_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bond_violations');
    }
};
