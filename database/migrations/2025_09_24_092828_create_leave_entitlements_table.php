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
        Schema::create('leave_entitlements', function (Blueprint $table) {
            $table->id();
            $table->char('employee_id', 36);
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->date('period_start'); // Tanggal mulai entitlement
            $table->date('period_end'); // Tanggal akhir entitlement
            $table->integer('entitled_days')->default(0); // Total hak cuti di periode
            $table->integer('withdrawable_days')->default(0); // Jumlah cuti yang bisa diambil/diuangkan
            $table->integer('deposit_days')->default(0); // Khusus periode pertama LSL
            $table->integer('carried_over')->default(0); // Sisa dari periode lama
            $table->integer('taken_days')->default(0); // Total cuti diambil
            $table->integer('remaining_days')->default(0); // Saldo akhir
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');

            // Unique constraint for employee, leave_type, and period combination
            $table->unique(['employee_id', 'leave_type_id', 'period_start', 'period_end'], 'leave_ent_unique_period');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_entitlements');
    }
};
