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
        Schema::create('employee_bonds', function (Blueprint $table) {
            $table->id(); // Auto increment ID
            $table->unsignedBigInteger('letter_number_id')->nullable(); // Foreign Key ke tabel 'letter_numbers'
            $table->string('letter_number')->nullable(); // Nomor surat ikatan dinas
            $table->string('employee_bond_number')->nullable(); // Nomor ikatan dinas karyawan
            $table->char('employee_id', 36); // Foreign Key ke tabel 'employees'
            $table->string('bond_name'); // Nama ikatan dinas
            $table->text('description')->nullable(); // Deskripsi detail ikatan dinas
            $table->date('start_date'); // Tanggal mulai ikatan dinas
            $table->date('end_date'); // Tanggal berakhir ikatan dinas
            $table->integer('total_bond_duration_months'); // Total durasi ikatan dinas dalam bulan
            $table->decimal('total_investment_value', 15, 2); // Total nilai investasi/penalty awal
            $table->enum('status', ['active', 'completed', 'violated', 'cancelled'])->default('active'); // Status ikatan dinas
            $table->string('document_path', 255)->nullable(); // Path ke dokumen perjanjian ikatan dinas
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('letter_number_id')->references('id')->on('letter_numbers')->onDelete('set null');

            // Indexes
            $table->index('employee_id');
            $table->index('letter_number_id');
            $table->index('status');
            $table->index(['start_date', 'end_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_bonds');
    }
};
