<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimeToOrderTrackings extends Migration
{
    public function up()
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->time('time')->nullable(); // Add the 'time' column, nullable by default
        });
    }

    public function down()
    {
        Schema::table('order_trackings', function (Blueprint $table) {
            $table->dropColumn('time'); // Remove the 'time' column on rollback
        });
    }
}
