<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameBranchDishToMenu extends Migration
{
    public function up()
    {
        Schema::rename('branch_dish', 'menu');
    }

    public function down()
    {
        Schema::rename('menu', 'branch_dish');
    }
}
