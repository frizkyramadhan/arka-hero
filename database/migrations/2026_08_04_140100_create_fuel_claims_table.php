<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('claim_number', 50)->unique();
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
            $table->decimal('total_quantity', 12, 2)->default(0);
            $table->decimal('total_cost', 15, 2)->default(0);
            $table->enum('status', ['draft', 'ready', 'sent', 'realized', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('realized_at')->nullable();
            $table->string('external_ref', 100)->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::table('fuel_records', function (Blueprint $table) {
            $table->foreign('fuel_claim_id')->references('id')->on('fuel_claims')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('fuel_records', function (Blueprint $table) {
            $table->dropForeign(['fuel_claim_id']);
        });

        Schema::dropIfExists('fuel_claims');
    }
};
