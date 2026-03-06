<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateNameColumnsInSupplyOrderReasonsTable extends Migration
{
    public function up()
    {
        Schema::table('supply_order_reasons', function (Blueprint $table) {
            // Drop old name column
            if (Schema::hasColumn('supply_order_reasons', 'name')) {
                $table->dropColumn('name');
            }

            // Add new columns
            $table->string('name_en')->after('id');
            $table->string('name_ar')->after('name_en');
        });
    }

    public function down()
    {
        Schema::table('supply_order_reasons', function (Blueprint $table) {
            // Rollback changes
            $table->dropColumn(['name_en', 'name_ar']);
            $table->string('name')->after('id');
        });
    }
}
