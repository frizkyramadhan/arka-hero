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
        Schema::create('leave_request_cancellations', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->char('leave_request_id', 36);
            $table->integer('days_to_cancel'); // Jumlah hari yang ingin di-cancel (bisa partial atau full)
            $table->text('reason'); // Alasan pembatalan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');

            // User yang mengajukan pembatalan
            $table->unsignedBigInteger('requested_by');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamp('requested_at');

            // HR yang mengkonfirmasi
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->foreign('confirmed_by')->references('id')->on('users')->onDelete('set null');
            $table->timestamp('confirmed_at')->nullable();
            $table->text('confirmation_notes')->nullable();

            $table->timestamps();

            // Foreign key constraint untuk leave_request_id
            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onDelete('cascade');

            // Indexes
            $table->index(['leave_request_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_cancellations');
    }
};
