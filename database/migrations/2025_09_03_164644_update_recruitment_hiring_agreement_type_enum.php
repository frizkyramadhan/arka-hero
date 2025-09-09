<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Update enum to include magang and harian
        DB::statement("ALTER TABLE recruitment_hiring MODIFY COLUMN agreement_type ENUM('pkwt', 'pkwtt', 'magang', 'harian') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert back to original enum
        DB::statement("ALTER TABLE recruitment_hiring MODIFY COLUMN agreement_type ENUM('pkwt', 'pkwtt') NOT NULL");
    }
};
