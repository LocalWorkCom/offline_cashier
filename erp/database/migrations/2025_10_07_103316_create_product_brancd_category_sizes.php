<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_brand_category_sizes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_brand_id');
            $table->unsignedBigInteger('category_size_id');

            $table->timestamps();

            // Foreign keys
            $table->foreign('product_brand_id')->references('id')->on('product_brands')->onDelete('cascade');
            $table->foreign('category_size_id')->references('id')->on('category_sizes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_brand_category_sizes');
    }
};