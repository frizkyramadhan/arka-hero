<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_disciplinaries', function (Blueprint $table) {
            $table->id();
            $table->char('employee_id', 36);
            $table->enum('type', ['coaching', 'counseling', 'sp1', 'sp2', 'sp3']);
            $table->date('effective_date');
            $table->date('end_date');
            $table->text('reason');
            $table->text('pp_notes')->nullable();
            $table->string('document_path', 255)->nullable();
            $table->enum('status', ['active', 'expired', 'superseded', 'terminated'])->default('active');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');

            $table->index(['employee_id', 'status']);
            $table->index(['end_date', 'status']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_disciplinaries');
    }
};
