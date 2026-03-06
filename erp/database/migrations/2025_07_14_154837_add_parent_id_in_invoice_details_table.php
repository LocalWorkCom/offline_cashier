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
        Schema::table('invoice_details', function (Blueprint $table) {
            $table->integer('details_id')->nullable()->after('invoice_id')->comment('order_detail_id or addon_id');
            $table->enum('type', ['addon', 'dish'])->default('dish')->after('details_id');
            $table->unsignedBigInteger('order_detail_id')->nullable()->comment('addon parent and take from order_detail_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_details', function (Blueprint $table) {
            //
        });
    }
};
