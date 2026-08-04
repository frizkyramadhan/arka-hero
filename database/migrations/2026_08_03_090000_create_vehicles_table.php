<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('kode', 50)->unique();
            $table->string('license_plate', 20)->unique();
            $table->string('pic')->nullable();
            $table->string('lokasi', 100)->nullable();
            $table->text('keterangan')->nullable();
            $table->string('brand', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('description')->nullable();
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('color', 50)->nullable();
            $table->enum('type', ['sedan', 'suv', 'mpv', 'truck', 'bus', 'motorcycle', 'pickup', 'other'])
                ->default('other');
            $table->enum('ownership', ['company', 'rental', 'employee'])->default('company');
            $table->string('vin', 100)->nullable();
            $table->string('engine_number', 100)->nullable();
            $table->enum('transmission', ['manual', 'automatic'])->nullable();
            $table->enum('fuel_type', ['gasoline', 'diesel', 'electric', 'hybrid', 'other'])->nullable();
            $table->unsignedInteger('capacity')->nullable();
            $table->enum('status', ['active', 'inactive', 'maintenance', 'sold', 'accident'])
                ->default('active');
            $table->unsignedInteger('odometer')->default(0);
            $table->uuid('assigned_to')->nullable();
            $table->unsignedBigInteger('arkfleet_equipment_id')->nullable()->index();
            $table->timestamp('arkfleet_sync_at')->nullable();
            $table->string('arkfleet_status', 50)->nullable();
            $table->string('project_code', 50)->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('lokasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
