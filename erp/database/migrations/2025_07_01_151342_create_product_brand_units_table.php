<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductBrandUnitsTable extends Migration
{
    public function up(): void
    {
        Schema::create('product_brand_units', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_brand_id');
            $table->unsignedBigInteger('first_unit_id');
            $table->unsignedBigInteger('second_unit_id');
            $table->decimal('factor', 10, 4); // e.g. 1 box = 10 items

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('product_brand_id')->references('id')->on('product_brands')->onDelete('cascade');
            $table->foreign('first_unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('second_unit_id')->references('id')->on('units')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_brand_units');
    }
}
