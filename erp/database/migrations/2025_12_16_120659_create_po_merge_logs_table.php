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
        Schema::create('po_merge_logs', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('merged_po_id')->comment('ID of the newly created merged PO');
            $table->json('old_po_ids')->comment('IDs of POs that were merged');
            $table->json('linked_prs')->nullable()->comment('PR IDs linked to old POs, if any');
            $table->json('items')->nullable()->comment('Details of items from merged POs');
            $table->json('deals')->nullable()->comment('Details of deals from merged POs');

            $table->unsignedBigInteger('created_by')->nullable()->comment('User who performed the merge');
            $table->timestamps();

            $table->foreign('merged_po_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('po_merge_logs', function (Blueprint $table) {
            //
        });
    }
};
