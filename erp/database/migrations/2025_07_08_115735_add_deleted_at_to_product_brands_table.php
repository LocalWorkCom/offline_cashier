<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class AddDeletedAtToProductBrandsTable extends Migration
{
    public function up()
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->softDeletes(); // adds a nullable deleted_at column
        });
    }

    public function down()
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropSoftDeletes(); // removes deleted_at column
        });
    }
}

