<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDefaultSizeToDishSizesTable extends Migration
{
    public function up()
    {
        Schema::table('dish_sizes', function (Blueprint $table) {
            $table->boolean('default_size')->default(false)->after('price');
        });
    }

    public function down()
    {
        Schema::table('dish_sizes', function (Blueprint $table) {
            $table->dropColumn('default_size');
        });
    }
}

