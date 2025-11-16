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
        Schema::table('recruitment_sessions', function (Blueprint $table) {
            // Make fptk_id nullable to support MPP as alternative source
            $table->uuid('fptk_id')->nullable()->change();

            // Add MPP Detail reference (UUID)
            $table->uuid('mpp_detail_id')->nullable()->after('fptk_id');

            // Foreign Key
            $table->foreign('mpp_detail_id')->references('id')->on('man_power_plan_details')->onDelete('cascade');

            // Index
            $table->index('mpp_detail_id');
        });

        // Add check constraint to ensure at least one source is provided
        // Note: Laravel doesn't support CHECK constraints directly, so we'll handle this in model validation
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop foreign key if exists
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = 'recruitment_sessions'
            AND COLUMN_NAME = 'mpp_detail_id'
            AND CONSTRAINT_NAME != 'PRIMARY'
        ");

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE recruitment_sessions DROP FOREIGN KEY {$foreignKey->CONSTRAINT_NAME}");
        }

        Schema::table('recruitment_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('recruitment_sessions', 'mpp_detail_id')) {
                $table->dropColumn('mpp_detail_id');
            }
        });
    }
};
