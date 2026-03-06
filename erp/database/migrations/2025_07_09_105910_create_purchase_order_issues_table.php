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
        Schema::create('purchase_order_issues', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('purchase_item_id');
            $table->decimal('quantity_affected', 10, 2);
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('issue_type_id');
            $table->enum('status', ['open', 'in-review', 'resolved'])->default('open');

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('purchase_item_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
            $table->foreign('unit_id')->references('id')->on('units')->onDelete('cascade');
            $table->foreign('issue_type_id')->references('id')->on('purchase_order_issue_types')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_order_issues');
    }
};
