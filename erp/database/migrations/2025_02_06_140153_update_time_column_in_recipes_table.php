<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTimeColumnInRecipesTable extends Migration
{
    public function up()
    {
        Schema::table('recipes', function (Blueprint $table) {
            // First, drop the existing 'time' column if it's of type TIME
            $table->dropColumn('time');
        });

        Schema::table('recipes', function (Blueprint $table) {
            // Add the 'time' column again as INTEGER (e.g., to store total minutes)
            $table->integer('time')->nullable();
        });
    }

    public function down()
    {
        Schema::table('recipes', function (Blueprint $table) {
            // Reverse the changes: drop the integer column and revert to TIME
            $table->dropColumn('time');
        });

        Schema::table('recipes', function (Blueprint $table) {
            $table->time('time')->nullable();
        });
    }
}
