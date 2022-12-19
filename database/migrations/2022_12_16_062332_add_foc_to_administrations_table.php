<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('administrations', function (Blueprint $table) {
            $table->string('no_sk_active')->nullable()->after('doh');
            $table->string('no_fptk')->nullable()->after('doh');
            $table->string('company_program')->nullable()->after('doh');
            $table->string('agreement')->nullable()->after('doh');
            $table->date('foc')->nullable()->after('doh');                                  
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('administrations', function (Blueprint $table) {
            $table->dropColumn('no_sk_active');
            $table->dropColumn('no_fptk');
            $table->dropColumn('company_program');
            $table->dropColumn('agreement');
            $table->dropColumn('foc');
           
           
            
           
        });
    }
};
