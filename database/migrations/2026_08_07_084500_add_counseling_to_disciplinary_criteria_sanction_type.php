<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE disciplinary_criteria MODIFY sanction_type ENUM('counseling', 'sp1', 'sp2', 'sp3') NOT NULL");
    }

    public function down(): void
    {
        DB::table('disciplinary_criteria')->where('sanction_type', 'counseling')->delete();
        DB::statement("ALTER TABLE disciplinary_criteria MODIFY sanction_type ENUM('sp1', 'sp2', 'sp3') NOT NULL");
    }
};
