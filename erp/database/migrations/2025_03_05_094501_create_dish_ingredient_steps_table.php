<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        Schema::create('dish_ingredient_steps', function (Blueprint $table) {
            $table->id();
            $table->json('recipe_steps');
            $table->text('recipe_title');
            $table->json('note_steps');
            $table->text('note_title');
            $table->unsignedBigInteger('dish_id')->nullable();
            $table->foreign('dish_id')->references('id')->on('dishes');
            $table->unsignedBigInteger('recipe_id')->nullable();
            $table->foreign('recipe_id')->references('id')->on('recipes');
    
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('dish_ingredient_steps');
    }
};
