<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('room_consumption_requests', 'start_date')) {
            Schema::table('room_consumption_requests', function (Blueprint $table) {
                $table->date('start_date')->nullable()->after('meeting_title');
                $table->date('end_date')->nullable()->after('start_date');
            });
        }

        if (Schema::hasColumn('room_consumption_requests', 'meeting_date')) {
            DB::statement('UPDATE room_consumption_requests SET start_date = COALESCE(start_date, meeting_date), end_date = COALESCE(end_date, meeting_date)');
        }

        // FK on meeting_room_id may rely on the compound (meeting_room_id, meeting_date) index.
        // Add a dedicated index first, then drop the compound index and meeting_date.
        $indexes = collect(DB::select('SHOW INDEX FROM room_consumption_requests'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        if (! in_array('room_consumption_requests_meeting_room_id_index', $indexes, true)
            && ! in_array('room_consumption_requests_meeting_room_id_foreign', $indexes, true)) {
            Schema::table('room_consumption_requests', function (Blueprint $table) {
                $table->index('meeting_room_id');
            });
            $indexes = collect(DB::select('SHOW INDEX FROM room_consumption_requests'))
                ->pluck('Key_name')
                ->unique()
                ->all();
        }

        if (in_array('room_consumption_requests_status_meeting_date_index', $indexes, true)) {
            Schema::table('room_consumption_requests', function (Blueprint $table) {
                $table->dropIndex(['status', 'meeting_date']);
            });
        }

        if (in_array('room_consumption_requests_meeting_room_id_meeting_date_index', $indexes, true)) {
            Schema::table('room_consumption_requests', function (Blueprint $table) {
                $table->dropIndex(['meeting_room_id', 'meeting_date']);
            });
        }

        if (Schema::hasColumn('room_consumption_requests', 'meeting_date')) {
            Schema::table('room_consumption_requests', function (Blueprint $table) {
                $table->dropColumn('meeting_date');
            });
        }

        DB::statement('ALTER TABLE room_consumption_requests MODIFY start_date DATE NOT NULL');
        DB::statement('ALTER TABLE room_consumption_requests MODIFY end_date DATE NOT NULL');

        $indexes = collect(DB::select('SHOW INDEX FROM room_consumption_requests'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('room_consumption_requests', function (Blueprint $table) use ($indexes) {
            if (! in_array('room_consumption_requests_status_start_date_index', $indexes, true)) {
                $table->index(['status', 'start_date']);
            }
            if (! in_array('rcr_room_start_end_idx', $indexes, true)) {
                $table->index(['meeting_room_id', 'start_date', 'end_date'], 'rcr_room_start_end_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('room_consumption_requests', 'meeting_date')) {
            Schema::table('room_consumption_requests', function (Blueprint $table) {
                $table->date('meeting_date')->nullable()->after('meeting_title');
            });
        }

        DB::statement('UPDATE room_consumption_requests SET meeting_date = start_date');

        $indexes = collect(DB::select('SHOW INDEX FROM room_consumption_requests'))
            ->pluck('Key_name')
            ->unique()
            ->all();

        Schema::table('room_consumption_requests', function (Blueprint $table) use ($indexes) {
            if (in_array('room_consumption_requests_status_start_date_index', $indexes, true)) {
                $table->dropIndex(['status', 'start_date']);
            }
            if (in_array('rcr_room_start_end_idx', $indexes, true)) {
                $table->dropIndex('rcr_room_start_end_idx');
            }
        });

        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->dropColumn(['start_date', 'end_date']);
        });

        DB::statement('ALTER TABLE room_consumption_requests MODIFY meeting_date DATE NOT NULL');

        Schema::table('room_consumption_requests', function (Blueprint $table) {
            $table->index(['status', 'meeting_date']);
            $table->index(['meeting_room_id', 'meeting_date']);
        });
    }
};
