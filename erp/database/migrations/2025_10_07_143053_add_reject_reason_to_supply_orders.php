<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRejectReasonToSupplyOrders extends Migration
{
    public function up()
    {
        Schema::table('supply_orders', function (Blueprint $table) {
            $table->foreignId('reject_reason_id')->nullable()->constrained('reject_reasons');
            $table->text('rejection_note')->nullable();
            
        });
    }

    public function down()
    {
        Schema::table('supply_orders', function (Blueprint $table) {
            $table->dropForeign(['reject_reason_id']);
            $table->dropColumn(['reject_reason_id', 'rejection_note']);
        });
    }
}

