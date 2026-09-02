<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supply_item_categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('prefix', 10)->unique();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
        });

        $now = now();
        DB::table('supply_item_categories')->insert([
            [
                'id' => (string) Str::uuid(),
                'name' => 'Office Supply',
                'prefix' => 'GAA',
                'description' => 'Stationery and office supplies',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => (string) Str::uuid(),
                'name' => 'Consumable',
                'prefix' => 'GAC',
                'description' => 'Consumable goods',
                'status' => 'active',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        Schema::create('supply_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 20)->unique();
            $table->uuid('supply_item_category_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('stock_unit', 50);
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->foreign('supply_item_category_id')->references('id')->on('supply_item_categories')->restrictOnDelete();
            $table->index(['supply_item_category_id', 'status']);
        });

        Schema::create('supply_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('order_number', 50);
            $table->unsignedInteger('order_sequence');
            $table->unsignedBigInteger('project_id');
            $table->unsignedBigInteger('administration_id');
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('requested_by');
            $table->date('order_date');
            $table->json('manual_approvers')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->restrictOnDelete();
            $table->foreign('administration_id')->references('id')->on('administrations')->restrictOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->restrictOnDelete();
            $table->foreign('requested_by')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['project_id', 'order_sequence']);
            $table->unique('order_number');
            $table->index(['status', 'project_id']);
        });

        Schema::create('supply_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supply_order_id');
            $table->uuid('supply_item_id');
            $table->unsignedInteger('quantity_ordered');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('supply_order_id')->references('id')->on('supply_orders')->cascadeOnDelete();
            $table->foreign('supply_item_id')->references('id')->on('supply_items')->restrictOnDelete();
            $table->index('supply_order_id');
        });

        Schema::create('supply_stock_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_number', 50);
            $table->unsignedInteger('document_sequence');
            $table->unsignedBigInteger('project_id');
            $table->date('stock_date');
            $table->text('notes')->nullable();
            $table->uuid('supply_order_id')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->restrictOnDelete();
            $table->foreign('supply_order_id')->references('id')->on('supply_orders')->nullOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['project_id', 'document_sequence']);
            $table->unique('document_number');
            $table->index('stock_date');
        });

        Schema::create('supply_stock_in_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supply_stock_in_id');
            $table->uuid('supply_item_id');
            $table->unsignedInteger('quantity');
            $table->uuid('supply_order_item_id')->nullable();
            $table->timestamps();

            $table->foreign('supply_stock_in_id')->references('id')->on('supply_stock_ins')->cascadeOnDelete();
            $table->foreign('supply_item_id')->references('id')->on('supply_items')->restrictOnDelete();
            $table->foreign('supply_order_item_id')->references('id')->on('supply_order_items')->nullOnDelete();
            $table->index(['supply_item_id', 'supply_stock_in_id']);
        });

        Schema::create('supply_stock_outs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('document_number', 50);
            $table->unsignedInteger('document_sequence');
            $table->unsignedBigInteger('project_id');
            $table->date('stock_date');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->restrictOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->restrictOnDelete();
            $table->unique(['project_id', 'document_sequence']);
            $table->unique('document_number');
            $table->index('stock_date');
        });

        Schema::create('supply_stock_out_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('supply_stock_out_id');
            $table->uuid('supply_item_id');
            $table->unsignedInteger('quantity');
            $table->string('location');
            $table->string('person_in_charge');
            $table->timestamps();

            $table->foreign('supply_stock_out_id')->references('id')->on('supply_stock_outs')->cascadeOnDelete();
            $table->foreign('supply_item_id')->references('id')->on('supply_items')->restrictOnDelete();
            $table->index(['supply_item_id', 'supply_stock_out_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supply_stock_out_items');
        Schema::dropIfExists('supply_stock_outs');
        Schema::dropIfExists('supply_stock_in_items');
        Schema::dropIfExists('supply_stock_ins');
        Schema::dropIfExists('supply_order_items');
        Schema::dropIfExists('supply_orders');
        Schema::dropIfExists('supply_items');
        Schema::dropIfExists('supply_item_categories');
    }
};
