<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('dish_ingredient_steps', function (Blueprint $table) {
            $table->dropForeign(['recipe_id']); // Drop the foreign key constraint
            $table->dropColumn('recipe_id'); // Drop the column itself
        });
    }

    public function down()
    {
        Schema::table('dish_ingredient_steps', function (Blueprint $table) {
            $table->unsignedBigInteger('recipe_id')->nullable();
            $table->foreign('recipe_id')->references('id')->on('recipes');
        });
    }
};
