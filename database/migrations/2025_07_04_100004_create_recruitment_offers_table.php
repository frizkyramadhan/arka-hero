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
        Schema::create('recruitment_offers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('session_id')->comment('Link to session');
            $table->string('offer_letter_number', 50)->unique();

            // Compensation Package
            $table->decimal('basic_salary', 15, 2);
            $table->json('allowances')->nullable()->comment('Transport, meal, etc allowances');
            $table->json('benefits')->nullable()->comment('Health insurance, BPJS, etc');

            // Employment Terms
            $table->integer('contract_duration')->nullable()->comment('months for PKWT');
            $table->integer('probation_period')->default(3)->comment('months');
            $table->date('start_date');
            $table->date('offer_valid_until');

            // Offer Status & Response
            $table->enum('status', ['draft', 'sent', 'accepted', 'rejected', 'expired', 'withdrawn'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->text('response_notes')->nullable();

            // Negotiation Tracking
            $table->json('negotiation_history')->nullable()->comment('Track negotiation rounds');

            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('session_id')->references('id')->on('recruitment_sessions')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users');

            // Indexes
            $table->index('status');
            $table->index('start_date');
            $table->index('offer_valid_until');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('recruitment_offers');
    }
};
