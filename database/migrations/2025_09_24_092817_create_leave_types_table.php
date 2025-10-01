<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Annual, LSL Staff, LSL Non Staff, LWP, LWOP
            $table->string('code')->unique(); // AL, LSL, LWP, LWOP
            $table->string('category'); // annual / special / unpaid
            $table->integer('default_days')->default(0); // Default entitlement
            $table->integer('eligible_after_years')->default(0); // Syarat minimal masa kerja
            $table->integer('deposit_days_first')->default(0); // Khusus LSL: 10 hari di periode pertama
            $table->boolean('carry_over')->default(false); // Bisa diakumulasi ke periode berikut
            $table->text('remarks')->nullable(); // Catatan khusus
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
