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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('product_id');
            $table->float('factor'); // Adjust precision as needed
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modify_by')->nullable();

            $table->unsignedBigInteger('deleted_by')->nullable();
            
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->foreign('modify_by')->references('id')->on('users');
            // Foreign key constraints
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('product_id')->references('id')->on('products');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product_units');
    }
};
