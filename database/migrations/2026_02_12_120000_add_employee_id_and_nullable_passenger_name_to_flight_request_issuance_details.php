<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds employee_id (nullable) to link passenger to employee from active administration,
     * and makes passenger_name nullable.
     */
    public function up(): void
    {
        Schema::table('flight_request_issuance_details', function (Blueprint $table) {
            $table->uuid('employee_id')->nullable()->after('detail_reservation');
            $table->string('passenger_name', 255)->nullable()->change();
        });

        Schema::table('flight_request_issuance_details', function (Blueprint $table) {
            $table->foreign('employee_id', 'fk_issuance_details_employee')
                ->references('id')
                ->on('employees')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('flight_request_issuance_details', function (Blueprint $table) {
            $table->dropForeign('fk_issuance_details_employee');
            $table->dropColumn('employee_id');
            $table->string('passenger_name', 255)->nullable(false)->change();
        });
    }
};
