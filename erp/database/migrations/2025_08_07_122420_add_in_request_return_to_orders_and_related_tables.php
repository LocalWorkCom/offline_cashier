<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('in_request_return')->default(0);
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->boolean('in_request_return')->default(0);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->boolean('in_request_return')->default(0);
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            $table->boolean('in_request_return')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('in_request_return');
        });

        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('in_request_return');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('in_request_return');
        });

        Schema::table('invoice_details', function (Blueprint $table) {
            $table->dropColumn('in_request_return');
        });
    }
};
