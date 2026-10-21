<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * letter_number_id was unique, so a cancelled FOA still blocked reuse of the
 * same letter. Keep the link for history; uniqueness applies only while the
 * FOA is not cancelled (MySQL unique allows many NULLs).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropForeign(['letter_number_id']);
            $table->dropUnique('vehicle_assignments_letter_number_id_unique');
            $table->index('letter_number_id');
            $table->foreign('letter_number_id')
                ->references('id')
                ->on('letter_numbers')
                ->nullOnDelete();
        });

        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('active_letter_number_id')
                ->nullable()
                ->virtualAs("IF(status = 'cancelled', NULL, letter_number_id)");
            $table->unique('active_letter_number_id');
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropUnique(['active_letter_number_id']);
            $table->dropColumn('active_letter_number_id');
        });

        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropForeign(['letter_number_id']);
            $table->dropIndex(['letter_number_id']);
            $table->unique('letter_number_id');
            $table->foreign('letter_number_id')
                ->references('id')
                ->on('letter_numbers')
                ->nullOnDelete();
        });
    }
};
