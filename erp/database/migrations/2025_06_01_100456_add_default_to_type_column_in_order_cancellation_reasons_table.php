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
        Schema::table('order_cancellation_reasons', function (Blueprint $table) {
            $table->json('type')->nullable(false)->change(); // NOT NULL, but no default
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('order_cancellation_reasons', function (Blueprint $table) {
            // Remove the default if rolling back
            $table->json('type')->default(null)->change();
        });
    }
};
