<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('ktp_document_path')->nullable();
            $table->string('kk_document_path')->nullable();
        });

        Schema::table('employeebanks', function (Blueprint $table) {
            $table->string('passbook_document_path')->nullable();
        });

        Schema::table('taxidentifications', function (Blueprint $table) {
            $table->string('npwp_document_path')->nullable();
        });

        Schema::table('insurances', function (Blueprint $table) {
            $table->string('document_path')->nullable();
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->string('document_path')->nullable();
        });

        Schema::table('educations', function (Blueprint $table) {
            $table->string('diploma_document_path')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['ktp_document_path', 'kk_document_path']);
        });

        Schema::table('employeebanks', function (Blueprint $table) {
            $table->dropColumn('passbook_document_path');
        });

        Schema::table('taxidentifications', function (Blueprint $table) {
            $table->dropColumn('npwp_document_path');
        });

        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn('document_path');
        });

        Schema::table('licenses', function (Blueprint $table) {
            $table->dropColumn('document_path');
        });

        Schema::table('educations', function (Blueprint $table) {
            $table->dropColumn('diploma_document_path');
        });
    }
};
