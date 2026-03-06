<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('dish_ingredient_steps', function (Blueprint $table) {
            $table->json('note_steps')->nullable()->change();
            $table->text('note_title')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('dish_ingredient_steps', function (Blueprint $table) {
            $table->json('note_steps')->nullable(false)->change();
            $table->text('note_title')->nullable(false)->change();
        });
    }
};

