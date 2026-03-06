<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceToBranchDishTable extends Migration
{
    public function up()
    {
        Schema::table('branch_dish', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(0.00)->after('dish_id');
        });
    }

    public function down()
    {
        Schema::table('branch_dish', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}
