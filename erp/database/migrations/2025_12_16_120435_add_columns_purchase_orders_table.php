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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->boolean('is_merged')->default(false)->after('status')->comment('Indicates if PO has been merged');
            $table->unsignedBigInteger('merged_po_id')->nullable()->after('is_merged')->comment('ID of the new merged PO');

            // Optional: Add foreign key constraint if you want
            $table->foreign('merged_po_id')->references('id')->on('purchase_orders')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            //
        });
    }
};
