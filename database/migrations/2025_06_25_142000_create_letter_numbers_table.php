<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('letter_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number', 50)->unique();
            $table->string('category_code', 10);
            $table->integer('sequence_number');
            $table->year('year');
            $table->foreignId('subject_id')->nullable()->constrained('letter_subjects');
            $table->string('custom_subject', 200)->nullable();
            $table->date('letter_date');
            $table->string('destination', 200)->nullable();
            $table->text('remarks')->nullable();

            // Integration fields
            $table->enum('status', ['reserved', 'used', 'cancelled'])->default('reserved');
            $table->string('related_document_type', 50)->nullable(); // 'officialtravel', 'future_letter_types'
            $table->unsignedBigInteger('related_document_id')->nullable(); // FK ke tabel surat terkait
            $table->timestamp('used_at')->nullable(); // kapan nomor digunakan untuk buat surat
            $table->foreignId('reserved_by')->constrained('users'); // user yang reserve nomor
            $table->foreignId('used_by')->nullable()->constrained('users'); // user yang gunakan nomor untuk buat surat

            // Fields khusus untuk setiap kategori - Updated to use administrations
            $table->foreignId('administration_id')->nullable()->constrained('administrations'); // Reference ke administrations table
            $table->foreignId('project_id')->nullable()->constrained('projects'); // Tetap ada untuk override jika perlu
            $table->string('duration', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('classification', ['Umum', 'Lembaga Pendidikan', 'Pemerintah'])->nullable();
            $table->enum('pkwt_type', ['PKWT I', 'PKWT II', 'PKWT III'])->nullable();
            $table->enum('par_type', ['new hire', 'promosi', 'mutasi', 'demosi'])->nullable();
            $table->enum('termination_reason', ['mengundurkan diri', 'termination', 'end of contract', 'end of project', 'pensiun', 'meninggal dunia'])->nullable();
            $table->enum('skpk_reason', ['PKWT Berakhir', 'Surat Pengalaman Kerja Hilang'])->nullable();
            $table->enum('ticket_classification', ['Pesawat', 'Kereta Api', 'Bus'])->nullable();

            $table->boolean('is_active')->default(1);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index(['category_code', 'year']);
            $table->index('letter_date');
            $table->index('administration_id');
            $table->index('status');
            $table->index(['related_document_type', 'related_document_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('letter_numbers');
    }
};
