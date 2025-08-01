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
        Schema::create('approval_plans', function (Blueprint $table) {
            $table->id();
            $table->char('document_id'); // uuid
            $table->string('document_type', 255); // officialtravel / recruitment_request
            $table->unsignedBigInteger('approver_id');
            $table->integer('status')->default(0); // pending=0 | approved=1 | revised=2 | rejected=3 | cancelled=4
            $table->string('remarks', 255)->nullable();
            $table->boolean('is_open')->default(true); // approval yg disetujui dihitung hanya yg is_open=true
            $table->boolean('is_read')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('approval_plans');
    }
};
