<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AddDeletedAtToProductBrandUnitsTable extends Migration
{
    public function up()
    {
        Schema::table('product_brand_units', function (Blueprint $table) {
            $table->softDeletes(); // Adds deleted_at column
        });
    }

    public function down()
    {
        Schema::table('product_brand_units', function (Blueprint $table) {
            $table->dropSoftDeletes(); // Removes deleted_at column
        });
    }
}
