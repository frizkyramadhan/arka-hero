<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_mutations', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('project_id')->constrained('projects');
            $table->date('mutated_at');
            $table->string('status')->default('active');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'mutated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_mutations');
    }
};
