<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->integer('invoice_number')->default(0);
            $table->enum('order_type', [
                'Delivery',
                'CallCenter',
                'Takeaway',
                'Online',
                'Dine-in',
                'Reservation-table'
            ])->nullable()->after('invoice_number');
        });
    }

    public function down()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'order_type']);
        });
    }
};