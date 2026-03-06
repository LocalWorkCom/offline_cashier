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
        Schema::create('store_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('store_id');
            $table->enum('type', [1,2])->default(1)->comment('1 for outgoing, 2 for incoming');
            $table->enum('to_type', [1,2,3,4])->default(1)->comment('1 from store, 2 from client, 3 from vender, 4 from employee');
            $table->integer('to_id')->comment('id for to_type column from users and vendors and stores');
            $table->date('date');
            $table->integer('total')->default(0);
            $table->decimal('total_price', 8,2)->default(0)->nullable();
            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('store_id')->references('id')->on('stores')->onUpdate('cascade');
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
        Schema::dropIfExists('store_transactions');
    }
};
