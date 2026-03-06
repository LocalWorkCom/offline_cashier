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
        Schema::table('store_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_invoice_id')->nullable();
            $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('store_transactions', function (Blueprint $table) {
            $table->dropColumn('purchase_invoice_id');
        });
    }
};
