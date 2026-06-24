<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_hiring', function (Blueprint $table) {
            $table->foreignUuid('employee_id')->nullable()->after('session_id')->constrained('employees')->nullOnDelete();
            $table->timestamp('employee_registered_at')->nullable()->after('reviewed_at');
            $table->unsignedBigInteger('employee_registered_by')->nullable()->after('employee_registered_at');

            $table->foreign('employee_registered_by')->references('id')->on('users')->nullOnDelete();
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_hiring', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['employee_registered_by']);
            $table->dropIndex(['employee_id']);
            $table->dropColumn(['employee_id', 'employee_registered_at', 'employee_registered_by']);
        });
    }
};
