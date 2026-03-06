<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('waiter_requests', function (Blueprint $table) {
            $table->unsignedBigInteger('coupon_id')->nullable()->after('table_id');
            $table->foreign('coupon_id')->references('id')->on('coupons')->onDelete('set null');
            $table->string('phone')->nullable()->after('coupon_id');
            $table->unsignedBigInteger('cashier_id')->nullable()->after('phone');
            $table->foreign('cashier_id')->references('id')->on('employees')->onDelete('set null');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waiter_requests', function (Blueprint $table) {
            //
        });
    }
};
