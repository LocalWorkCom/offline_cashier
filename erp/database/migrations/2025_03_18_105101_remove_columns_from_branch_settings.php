<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->dropColumn(['takeaway_deposit_value_type', 'table_cancelation_time_type']);
        });
    }

    public function down()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->tinyInteger('takeaway_deposit_value_type')->default(0);
            $table->tinyInteger('table_cancelation_time_type')->default(0);
        });
    }
};

