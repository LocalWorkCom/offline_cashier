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
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->decimal('table_cancelation_time_allowed', 8, 2)->required()->default(0.0)->change();
            $table->decimal('takeaway_deposit_value_if_1', 8, 2)->required()->default(0.0)->change();
        });
    }

    public function down()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->decimal('table_cancelation_time_allowed', 8, 2)->required(false)->default(0.0)->change();
            $table->decimal('takeaway_deposit_value_if_1', 8, 2)->required(false)->default(0.0)->change();
        });
    }
};
