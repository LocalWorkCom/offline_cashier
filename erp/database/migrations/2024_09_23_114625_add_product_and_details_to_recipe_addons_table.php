<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recipe_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->after('addon_id');
            $table->unsignedBigInteger('product_unit_id')->after('product_id');
            $table->decimal('quantity', 8, 2)->after('product_unit_id');
            $table->decimal('price', 8, 2)->after('quantity');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_addons', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_unit_id']);
            $table->dropColumn(['product_id', 'product_unit_id', 'quantity', 'price']);
        });
    }
};
