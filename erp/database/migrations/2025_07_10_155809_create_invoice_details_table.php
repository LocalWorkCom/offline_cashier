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
        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('order_detail_id')->nullable();
            $table->integer('quantity')->nullable();

            $table->decimal('tax', 8, 2)->default(0)->nullable();
            $table->decimal('service_fees', 8, 2)->default(0)->nullable();
            $table->decimal('coupon_value', 8, 2)->default(0)->nullable();
            $table->decimal('total_before_tax', 8, 2)->default(0)->nullable();
            $table->decimal('total_before_coupon', 8, 2)->default(0)->nullable();
            $table->decimal('total_after_tax', 8, 2)->default(0)->nullable();
            $table->text('note')->nullable();

            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onUpdate('cascade');
            $table->foreign('order_detail_id')->references('id')->on('order_details')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('employees')->onUpdate('cascade');        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_details');
    }
};
