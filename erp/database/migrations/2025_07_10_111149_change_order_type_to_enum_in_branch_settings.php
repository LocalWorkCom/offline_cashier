<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeOrderTypeToEnumInBranchSettings extends Migration
{
    public function up()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->enum('order_type', [
                'takeaway', 
                'delivery', 
                'dine-in', 
                'reservation_with_order', 
                'reservation_without_order'
            ])->default('takeaway')->change();
        });
    }

    public function down()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->string('order_type')->change();
        });
    }
}