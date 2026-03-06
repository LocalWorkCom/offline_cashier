<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('order_addons', function (Blueprint $table) {

            $table->id();
            $table->float('price')->nullable();
            $table->integer('quantity');

            // Create an unsignedBigInteger column for order_id
            $table->unsignedBigInteger('order_id');
            
            // Create an unsignedBigInteger column for recipe_addon_id
            $table->unsignedBigInteger('recipe_addon_id');
            
            // Create foreign key constraints for both order_id and recipe_addon_id
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('recipe_addon_id')->references('id')->on('recipe_addons')->onDelete('cascade');
            
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modify_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modify_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');

            $table->softDeletes(); // Place this column after the 'updated_at' column

            // Create timestamps (created_at and updated_at)
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('order_addons');
    }
};
