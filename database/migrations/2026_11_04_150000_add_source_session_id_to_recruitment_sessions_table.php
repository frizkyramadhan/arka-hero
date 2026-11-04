<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_sessions', function (Blueprint $table) {
            if (! Schema::hasColumn('recruitment_sessions', 'source_session_id')) {
                $table->uuid('source_session_id')->nullable()->after('candidate_id')
                    ->comment('Session this process was continued/copied from');
                $table->foreign('source_session_id')
                    ->references('id')
                    ->on('recruitment_sessions')
                    ->nullOnDelete();
                $table->index('source_session_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_sessions', function (Blueprint $table) {
            if (Schema::hasColumn('recruitment_sessions', 'source_session_id')) {
                $table->dropForeign(['source_session_id']);
                $table->dropIndex(['source_session_id']);
                $table->dropColumn('source_session_id');
            }
        });
    }
};
