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
        Schema::create('product_limit', function (Blueprint $table) {
            $table->id();
            $table->decimal('min_limit', 8, 2);
            $table->decimal('max_limit', 8, 2);

            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('store_category_id')->nullable();
            $table->foreign('store_category_id')->references('id')->on('store_categories');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_limit');
    }
};
