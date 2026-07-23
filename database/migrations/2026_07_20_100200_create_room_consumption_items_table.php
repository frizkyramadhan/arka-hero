<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('room_consumption_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('request_id');
            $table->string('consumption_type', 40);
            $table->boolean('is_selected')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('request_id')
                ->references('id')
                ->on('room_consumption_requests')
                ->cascadeOnDelete();
            $table->unique(['request_id', 'consumption_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('room_consumption_items');
    }
};
