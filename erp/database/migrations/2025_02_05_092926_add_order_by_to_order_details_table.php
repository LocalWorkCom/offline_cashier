<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->integer('order_by')->nullable()->default(-1)->comment('-1 means all orders will get out');
        });
    }

    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('order_by');
        });
    }
};

