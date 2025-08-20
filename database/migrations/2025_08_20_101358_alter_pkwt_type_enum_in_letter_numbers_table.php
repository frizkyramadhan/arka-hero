<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add new column with new enum values
        DB::statement("ALTER TABLE letter_numbers ADD COLUMN pkwt_type_new ENUM('PKWT', 'PKWTT') NULL AFTER pkwt_type");

        // Copy data from old column to new column with mapping
        DB::statement("
            UPDATE letter_numbers
            SET pkwt_type_new = CASE
                WHEN pkwt_type = 'PKWT I' THEN 'PKWT'
                WHEN pkwt_type = 'PKWT II' THEN 'PKWT'
                WHEN pkwt_type = 'PKWT III' THEN 'PKWT'
                ELSE pkwt_type
            END
        ");

        // Drop old column and rename new column
        DB::statement("ALTER TABLE letter_numbers DROP COLUMN pkwt_type");
        DB::statement("ALTER TABLE letter_numbers CHANGE pkwt_type_new pkwt_type ENUM('PKWT', 'PKWTT') NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Add old column back
        DB::statement("ALTER TABLE letter_numbers ADD COLUMN pkwt_type_old ENUM('PKWT I', 'PKWT II', 'PKWT III') NULL AFTER pkwt_type");

        // Copy data back with reverse mapping
        DB::statement("
            UPDATE letter_numbers
            SET pkwt_type_old = CASE
                WHEN pkwt_type = 'PKWT' THEN 'PKWT I'
                WHEN pkwt_type = 'PKWTT' THEN 'PKWT II'
                ELSE pkwt_type
            END
        ");

        // Drop new column and rename old column back
        DB::statement("ALTER TABLE letter_numbers DROP COLUMN pkwt_type");
        DB::statement("ALTER TABLE letter_numbers CHANGE pkwt_type_old pkwt_type ENUM('PKWT I', 'PKWT II', 'PKWT III') NULL");
    }
};
