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
        Schema::table('recruitment_requests', function (Blueprint $table) {
            // Add approval-related fields first
            $table->timestamp('submit_at')->nullable();
            $table->timestamp('approved_at')->nullable();
        });

        // Rename column in separate operation
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->renameColumn('status', 'status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Rename column back first
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->renameColumn('status', 'status');
        });

        // Then drop the added columns
        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->dropColumn(['submit_at', 'approved_at']);
        });
    }
};
