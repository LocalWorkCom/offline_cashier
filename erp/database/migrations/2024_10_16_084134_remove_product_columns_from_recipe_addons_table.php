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
            // Drop foreign keys first
            $table->dropForeign(['product_id']);
            $table->dropForeign(['product_unit_id']);

            // Then drop the columns
            $table->dropColumn(['product_id', 'product_unit_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recipe_addons', function (Blueprint $table) {
            // Add columns back in down method
            $table->unsignedBigInteger('product_id')->after('recipe_id');
            $table->unsignedBigInteger('product_unit_id')->after('product_id');

            // Restore foreign keys
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('product_unit_id')->references('id')->on('product_units')->onDelete('cascade');
        });
    }
};
