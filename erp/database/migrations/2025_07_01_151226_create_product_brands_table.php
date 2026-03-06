<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBrandsTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_brands', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('brand_id');        // Foreign key to brands
            $table->unsignedBigInteger('product_id');      // Foreign key to products

            $table->string('image')->nullable();
            $table->boolean('is_reusable')->default(false);
            $table->string('barcode')->nullable();
            $table->boolean('is_have_expired')->default(false);

            $table->enum('expiration_type', ['days', 'dates'])->nullable();

            $table->timestamps();

            // Foreign key constraints (optional, remove if not needed)
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_brands');
    }
}
