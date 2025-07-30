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
            // Drop the existing status column
            $table->dropColumn('status');
        });

        Schema::table('officialtravels', function (Blueprint $table) {
            // Add new enum status column
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected', 'cancelled', 'closed'])->default('draft')->after('id');
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
            // Drop the enum status column
            $table->dropColumn('status');
        });

        Schema::table('officialtravels', function (Blueprint $table) {
            // Add back the string status column
            $table->string('status')->default('draft')->after('id');
        });
    }
};
