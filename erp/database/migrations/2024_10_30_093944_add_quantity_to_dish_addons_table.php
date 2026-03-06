<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuantityToDishAddonsTable extends Migration
{
    public function up()
    {
        Schema::table('dish_addons', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('addon_id');
        });
    }

    public function down()
    {
        Schema::table('dish_addons', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
}
