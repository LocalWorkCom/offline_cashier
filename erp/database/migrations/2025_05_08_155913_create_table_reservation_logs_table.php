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
        Schema::create('table_reservation_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_reservation_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->unsignedBigInteger('cashier_id')->nullable();
            $table->unsignedBigInteger('waiter_id')->nullable();
            $table->date('date')->nullable();
            $table->time('from')->nullable();
            $table->time('to')->nullable();
            $table->enum('canceled', [0,1])->default()->nullable()->comment('0 for not canceled, 1 for canceled');
            $table->text('canceled_reason')->nullable();
            $table->unsignedBigInteger('canceled_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('table_reservation_id')->references('id')->on('table_reservations')->onUpdate('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('cashier_id')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('waiter_id')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('canceled_by')->references('id')->on('users')->onUpdate('cascade');
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
        Schema::dropIfExists('table_reservation_logs');
    }
};
