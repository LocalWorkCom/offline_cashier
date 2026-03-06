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
        Schema::dropIfExists('orders'); // Drop the table if it already exists
        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');

            $table->enum('type', ['takeaway', 'online', 'in_resturant'])->default('in_resturant');

            $table->string('note')->nullable();

            $table->string('order_number');

            $table->float('tax_value')->nullable();

            $table->float('fees')->nullable();

            $table->float('delivery_fees')->nullable();

            $table->float('total_price_befor_tax');

            $table->float('total_price_after_tax');

            $table->unsignedBigInteger('client_id');
            
            $table->foreign('client_id')->references('id')->on('users');
            
            $table->unsignedBigInteger('table_id');
            
            $table->foreign('table_id')->references('id')->on('tables');

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modify_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modify_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            
            // Foreign key constraints
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

        Schema::dropIfExists('orders');

        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
    }
};
