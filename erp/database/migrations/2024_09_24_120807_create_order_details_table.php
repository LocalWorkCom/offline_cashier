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
        Schema::disableForeignKeyConstraints(); // Disable foreign key checks
        Schema::dropIfExists('order_details'); // Drop the table if it already exists
        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
        Schema::create('order_details', function (Blueprint $table) {

            $table->id();

            $table->enum('status', ['refund', 'completed'])->default('completed');

            $table->integer('quantity')->nullable();

            $table->float('total');

            $table->float('price_befor_tax');

            $table->float('price_after_tax');

            $table->float('tax_value')->nullable();

            $table->string('note')->nullable();

            $table->unsignedBigInteger('order_id');

            $table->unsignedBigInteger('addon_id');

            $table->unsignedBigInteger('unit_id');

            $table->unsignedBigInteger('recipe_id');

            $table->unsignedBigInteger('product_id');

            $table->foreign('order_id')->references('id')->on('orders');
            $table->foreign('addon_id')->references('id')->on('addons');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('recipe_id')->references('id')->on('recipes');
            $table->foreign('product_id')->references('id')->on('products');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modify_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modify_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->timestamps();

            $table->softDeletes(); // Place this column after the 'updated_at' column

            // Foreign key constraints

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints(); // Disable foreign key checks

        Schema::dropIfExists('order_details');

        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
    }
};
