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
        Schema::table('officialtravels', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['recommendation_by']);
            $table->dropForeign(['approval_by']);

            // Remove legacy recommendation fields
            $table->dropColumn([
                'recommendation_status',
                'recommendation_remark',
                'recommendation_by',
                'recommendation_date',
                'recommendation_timestamps'
            ]);

            // Remove legacy approval fields
            $table->dropColumn([
                'approval_status',
                'approval_remark',
                'approval_by',
                'approval_date',
                'approval_timestamps'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('officialtravels', function (Blueprint $table) {
            // Re-add legacy recommendation fields
            $table->enum('recommendation_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('recommendation_remark')->nullable();
            $table->foreignId('recommendation_by')->nullable()->constrained('users');
            $table->datetime('recommendation_date')->nullable();
            $table->timestamp('recommendation_timestamps')->nullable();

            // Re-add legacy approval fields
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('approval_remark')->nullable();
            $table->foreignId('approval_by')->nullable()->constrained('users');
            $table->datetime('approval_date')->nullable();
            $table->timestamp('approval_timestamps')->nullable();
        });
    }
};
