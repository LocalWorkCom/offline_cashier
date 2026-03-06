<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->string('reference_number')->nullable(); // or after another column if preferred
        });
    }

    public function down()
    {
        Schema::table('order_transactions', function (Blueprint $table) {
            $table->dropColumn('reference_number');
        });
    }
};
