<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_disciplinary_criterion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_disciplinary_id');
            $table->unsignedBigInteger('disciplinary_criterion_id');
            $table->timestamps();

            $table->foreign('employee_disciplinary_id', 'edc_disciplinary_fk')
                ->references('id')
                ->on('employee_disciplinaries')
                ->onDelete('cascade');
            $table->foreign('disciplinary_criterion_id', 'edc_criterion_fk')
                ->references('id')
                ->on('disciplinary_criteria')
                ->onDelete('restrict');

            $table->unique(
                ['employee_disciplinary_id', 'disciplinary_criterion_id'],
                'edc_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_disciplinary_criterion');
    }
};
