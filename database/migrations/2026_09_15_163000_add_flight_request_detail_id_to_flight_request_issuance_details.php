<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('flight_request_issuance_details', function (Blueprint $table) {
            $table->uuid('flight_request_detail_id')->nullable()->after('ticket_order');
            $table->foreign('flight_request_detail_id', 'fk_issuance_details_fr_detail')
                ->references('id')
                ->on('flight_request_details')
                ->nullOnDelete();
            $table->index('flight_request_detail_id', 'idx_issuance_details_fr_detail');
        });
    }

    public function down(): void
    {
        Schema::table('flight_request_issuance_details', function (Blueprint $table) {
            $table->dropForeign('fk_issuance_details_fr_detail');
            $table->dropIndex('idx_issuance_details_fr_detail');
            $table->dropColumn('flight_request_detail_id');
        });
    }
};
