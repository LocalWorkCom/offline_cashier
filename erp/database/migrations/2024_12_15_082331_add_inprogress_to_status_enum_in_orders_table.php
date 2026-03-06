<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInprogressToStatusEnumInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Modify the ENUM column by recreating it with the new values
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending','completed','cancelled','inprogress'])
                ->default('pending')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revert to the original ENUM values
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending','inprogress', 'cancelled', 'completed'])
                ->default('pending')
                ->change();
        });
    }
}
