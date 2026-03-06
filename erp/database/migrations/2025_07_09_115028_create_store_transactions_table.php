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
        Schema::create('store_transactions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('purchase_item_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->enum('transaction_type', ['in', 'out'])->default('in');
            $table->unsignedBigInteger('transaction_reason_id')->nullable();
            $table->unsignedBigInteger('unit_id')->nullable();
            $table->unsignedBigInteger('product_store_id')->nullable();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('sku')->nullable();
            $table->date('production_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->integer('days_to_expiry')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('store_id')->references('id')->on('stores')->onDelete('set null');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_order_items')->onDelete('set null');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->foreign('transaction_reason_id')->references('id')->on('transaction_reasons')->onDelete('set null');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('set null');
            $table->foreign('product_store_id')->references('id')->on('product_stores')->onDelete('set null');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('set null');
            $table->foreign('created_by')->references(columns: 'id')->on('employees')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_transactions');
    }
};
