<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_records', function (Blueprint $table) {
            $table->enum('status', ['submitted', 'verified', 'rejected', 'claimed'])
                ->default('submitted')
                ->after('notes');
            $table->unsignedBigInteger('verified_by')->nullable()->after('status');
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_notes')->nullable()->after('verified_at');
            $table->timestamp('rejected_at')->nullable()->after('verification_notes');
            $table->uuid('fuel_claim_id')->nullable()->after('rejected_at');
            $table->timestamp('ai_parsed_at')->nullable()->after('fuel_claim_id');
            $table->string('ai_model', 100)->nullable()->after('ai_parsed_at');
            $table->json('ai_raw_json')->nullable()->after('ai_model');

            $table->index('status');
            $table->index('fuel_claim_id');
        });
    }

    public function down(): void
    {
        Schema::table('fuel_records', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['fuel_claim_id']);
            $table->dropColumn([
                'status',
                'verified_by',
                'verified_at',
                'verification_notes',
                'rejected_at',
                'fuel_claim_id',
                'ai_parsed_at',
                'ai_model',
                'ai_raw_json',
            ]);
        });
    }
};
