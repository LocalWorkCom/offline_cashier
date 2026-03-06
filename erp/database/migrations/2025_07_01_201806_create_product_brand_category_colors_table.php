<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBrandCategoryColorsTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_brand_category_colors', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_brand_id');
            $table->unsignedBigInteger('category_color_id');

            $table->timestamps();

            // Foreign keys
            $table->foreign('product_brand_id')->references('id')->on('product_brands')->onDelete('cascade');
            $table->foreign('category_color_id')->references('id')->on('category_colors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_brand_category_colors');
    }
}
