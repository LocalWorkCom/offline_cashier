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
        Schema::create('kitchen_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_details_id')->nullable();
            $table->unsignedBigInteger('dish_id')->nullable();
            $table->unsignedBigInteger('dish_size_id')->nullable();
            $table->unsignedBigInteger('offer_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('store_id')->nullable();
            $table->unsignedBigInteger('order_addone_id')->nullable();
            $table->unsignedBigInteger('dish_addone_id')->nullable();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->integer('quantity')->default(0)->nullable();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'inprogress'])->default('pending')->nullable();
            $table->enum('order_type',['Delivery', 'Takeaway', 'InResturant'])->default('InResturant')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onUpdate('cascade');
            $table->foreign('order_details_id')->references('id')->on('order_details')->onUpdate('cascade');
            $table->foreign('dish_id')->references('id')->on('dishes')->onUpdate('cascade');
            $table->foreign('dish_size_id')->references('id')->on('dish_sizes')->onUpdate('cascade');
            $table->foreign('offer_id')->references('id')->on('offers')->onUpdate('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onUpdate('cascade');
            $table->foreign('order_addone_id')->references('id')->on('order_addons')->onUpdate('cascade');
            $table->foreign('dish_addone_id')->references('id')->on('dish_addons')->onUpdate('cascade');
            $table->foreign('table_id')->references('id')->on('tables')->onUpdate('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kitchen_logs');
    }
};
