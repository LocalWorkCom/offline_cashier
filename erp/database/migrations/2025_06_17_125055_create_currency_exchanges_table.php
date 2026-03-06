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
        Schema::create('currency_exchanges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('exchange_currency_id')->nullable();
            $table->double('exchange_value', 15, 6)->nullable();
            $table->timestamp('date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('currency_id')->references('id')->on('currencies')->onUpdate('cascade');
            $table->foreign('exchange_currency_id')->references('id')->on('currencies')->onUpdate('cascade');
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
        Schema::dropIfExists('currency_exchanges');
    }
};
