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
        Schema::disableForeignKeyConstraints();

        // Drop old tables
        Schema::dropIfExists('purchase_order_items');
        Schema::enableForeignKeyConstraints();

        Schema::table('purchase_orders', function (Blueprint $table) {

            $table->unsignedBigInteger('reject_reason_id')
                ->nullable()
                ->after('rejected_from');

            $table->foreign('reject_reason_id')
                ->references('id')
                ->on('reject_purchase_requests')
                ->nullOnDelete();
        });
        Schema::create('purchase_order_items', function (Blueprint $table) {

            $table->id();

            // Foreign Keys
            $table->unsignedBigInteger('po_id');           // purchase_orders
            $table->unsignedBigInteger('product_id');      // products
            $table->unsignedBigInteger('category_id');     // categories
            $table->unsignedBigInteger('brand_id');        // brands
            $table->unsignedBigInteger('unit_id');         // units

            // Data fields
            $table->decimal('quantity', 15, 2)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('sub_total', 15, 2)->default(0);

            $table->text('notes')->nullable();
            $table->foreign('po_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->restrictOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->restrictOnDelete();
            $table->foreign('brand_id')->references('id')->on('brands')->restrictOnDelete();
            $table->foreign('unit_id')->references('id')->on('units')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('purchase_order_deals', function (Blueprint $table) {

            $table->id();

            // Foreign Keys
            $table->unsignedBigInteger('po_id');          // purchase_orders
            $table->unsignedBigInteger('price_deal_id');  // pricing_deals

            // Status
            $table->enum('status', ['pending', 'accept_pm', 'accept_fm', 'rejected'])
                ->default('pending');
            $table->foreign('po_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->foreign('price_deal_id')->references('id')->on('pricing_deals')->restrictOnDelete();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
