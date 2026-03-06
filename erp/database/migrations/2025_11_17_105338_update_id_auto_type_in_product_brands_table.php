<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


return new class extends Migration
{
    public function up()
    {
        Schema::table('product_brands', function (Blueprint $table) {
            // Change id_auto from BIGINT to STRING
            $table->string('id_auto', 100)->change();
        });
    }

    public function down()
    {
        Schema::table('product_brands', function (Blueprint $table) {
            // Rollback to BIGINT if needed
            $table->bigInteger('id_auto')->change();
        });
    }
};
