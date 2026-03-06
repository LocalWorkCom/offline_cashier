<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTimeFieldsToIntegerInBranchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            // Change the column type from time to integer
            $table->integer('time_cancellation')->nullable()->change();
            $table->integer('delivery_time')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            // Revert the column type back to time
            $table->time('time_cancellation')->nullable()->change();
            $table->time('delivery_time')->nullable()->change();
        });
    }
}
