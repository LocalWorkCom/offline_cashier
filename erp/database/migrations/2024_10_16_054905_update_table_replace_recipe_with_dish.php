<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTableReplaceRecipeWithDish extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            // Drop foreign key constraint and the 'recipe_id' column
            $table->dropForeign(['recipe_id']);
            $table->dropColumn('recipe_id');

            // Add 'dish_id' column and set up the foreign key constraint
            $table->unsignedBigInteger('dish_id')->nullable();
            $table->foreign('dish_id')->references('id')->on('dishes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            // Drop the 'dish_id' foreign key and column
            $table->dropForeign(['dish_id']);
            $table->dropColumn('dish_id');

            // Re-add 'recipe_id' column and foreign key constraint
            $table->unsignedBigInteger('recipe_id')->nullable();
            $table->foreign('recipe_id')->references('id')->on('recipes');
        });
    }
}
