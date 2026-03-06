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
        Schema::dropIfExists('product_transactions');

        //Create the new product_transactions table
        Schema::create('product_transactions', function (Blueprint $table) {
            $table->id();

            // Foreign key to product_brands
            $table->unsignedBigInteger('product_brand_id');
            $table->foreign('product_brand_id')
                ->references('id')
                ->on('product_brands')
                ->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('barcode')->nullable();
            $table->date('production_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
        Schema::dropIfExists('opening_balance');

        //Create the new product_transactions table
        Schema::create('product_opening_balance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_brand_id');
            $table->foreign('product_brand_id')
                ->references('id')
                ->on('product_brands')
                ->cascadeOnDelete();
            $table->decimal('quantity', 10, 2)->default(0);
            $table->string('barcode')->nullable();
            $table->date('production_date')->nullable();
            $table->date('expiration_date')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();
        });
        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropColumn('barcode');
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
