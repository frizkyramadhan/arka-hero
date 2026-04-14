<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Permintaan lembur — header (project, tanggal, status termasuk finished oleh HR).
     */
    public function up(): void
    {
        Schema::create('overtime_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->date('overtime_date');
            $table->string('status', 32)->default('draft')->index();
            // draft | pending | approved | rejected | finished
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->foreignId('finished_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('finished_remarks')->nullable();
            $table->json('manual_approvers')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status']);
            $table->index(['status', 'overtime_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('overtime_requests');
    }
};
