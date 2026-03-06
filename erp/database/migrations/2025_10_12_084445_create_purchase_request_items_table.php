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
        Schema::create('purchase_request_items', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('purchase_request_id');
            $table->unsignedBigInteger('product_id');
            $table->bigInteger('ordered_quantity');
            $table->unsignedBigInteger('unit_id');
            $table->string('note')->nullable();
            $table->bigInteger('current_stock_level')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('product_brands')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_request_items');
    }
};
