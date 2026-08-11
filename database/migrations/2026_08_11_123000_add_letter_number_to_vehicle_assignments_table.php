<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicle_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('letter_number_id')->nullable()->after('form_number');
            $table->string('letter_number', 50)->nullable()->after('letter_number_id');

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
            $table->dropColumn(['letter_number_id', 'letter_number']);
        });
    }
};
