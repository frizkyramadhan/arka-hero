<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Letter numbers are unique per (letter_number, year, project_id), so the same
 * FOA string (e.g. FOA4964) can exist for HO and APS. vehicle_assignments.form_number
 * must not be globally unique — uniqueness is per letter_number_id instead.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropUnique('vehicle_assignments_form_number_unique');
            $table->index('form_number');

            // Rebuild FK on a unique index so one letter maps to at most one FOA.
            $table->dropForeign(['letter_number_id']);
            $table->unique('letter_number_id');
            $table->foreign('letter_number_id')
                ->references('id')
                ->on('letter_numbers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->dropForeign(['letter_number_id']);
            $table->dropUnique(['letter_number_id']);
            $table->foreign('letter_number_id')
                ->references('id')
                ->on('letter_numbers')
                ->nullOnDelete();

            $table->dropIndex(['form_number']);
            $table->unique('form_number');
        });
    }
};
