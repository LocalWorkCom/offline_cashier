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
        Schema::table('cashier_settings', function (Blueprint $table) {
            $table->time('time_auto_send_to_portal')->nullable(); // Replace with actual column name
        });
    }

    public function down()
    {
        Schema::table('cashier_settings', function (Blueprint $table) {
            $table->dropColumn('time_auto_send_to_portal');
        });
    }
};
