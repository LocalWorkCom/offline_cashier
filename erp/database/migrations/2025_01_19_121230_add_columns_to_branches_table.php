<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToBranchesTable extends Migration
{
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->tinyInteger('tax_application')->default(0)->comment('0:not included tax, 1:included tax');
            $table->tinyInteger('coupon_application')->default(0)->comment('0:before tax, 1:after tax');
            $table->decimal('tax_percentage', 5, 2)->default(0.00);
            $table->time('time_cancellation')->nullable();
            $table->time('delivery_time')->nullable();
            $table->decimal('services_fees', 10, 2)->default(0.00)->nullable();
            $table->integer('tax_apply')->nullable()->comment('0:not tax apply, 1:tax apply');
        });
    }

    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn([
                'tax_application',
                'coupon_application',
                'tax_percentage',
                'time_cancellation',
                'delivery_time',
                'services_fees',
                'tax_apply',
            ]);
        });
    }
}

