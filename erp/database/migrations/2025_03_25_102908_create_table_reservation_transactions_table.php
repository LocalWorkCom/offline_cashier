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
        Schema::create('table_reservation_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_reservation_id')->nullable();
            $table->enum('payment_status', ['paid', 'unpaid', 'part', 'payment_failed'])->default('unpaid')->nullable();
            $table->enum('payment_method', ['cash', 'credit_card', 'online'])->default('cash')->nullable();
            $table->double('paid')->default(0)->nullable();
            $table->date('date')->nullable();
            $table->double('refund')->default(0)->nullable();
            $table->integer('is_refund')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('payment_gateway_reference')->nullable();
            $table->date('payment_gateway_date')->nullable();
            $table->string('payment_gateway_currency')->nullable();
            $table->string('payment_gateway_status')->nullable();
            $table->string('payment_gateway_method')->nullable();
            $table->string('reason')->nullable();
            $table->softDeletes();

            $table->foreign('table_reservation_id')->references('id')->on('table_reservations')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_reservation_transactions');
    }
};
