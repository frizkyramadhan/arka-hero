<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * - Change status column from ENUM to string (VARCHAR) for flexibility.
     * - Add submitted_by_user: user-submitted LOT (from my-travels) stored as draft; this flag marks them for HR confirmation.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE officialtravels MODIFY COLUMN status VARCHAR(255) DEFAULT 'draft'");

        Schema::table('officialtravels', function (Blueprint $table) {
            $table->boolean('submitted_by_user')->default(false)->after('status');
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
            $table->dropColumn('submitted_by_user');
        });

        DB::statement("ALTER TABLE officialtravels MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected', 'cancelled', 'closed') DEFAULT 'draft'");
    }
};
