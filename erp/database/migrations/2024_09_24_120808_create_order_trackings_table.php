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
        Schema::dropIfExists('order_trackings'); // Drop the table if it already exists
        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
        Schema::create('order_trackings', function (Blueprint $table) {

            $table->id();

            $table->enum('order_status', ['pending', 'in_progress' ,'completed','on_way', 'deliverd' ,'cancelled'])->default('pending');

            $table->unsignedBigInteger('order_id');
            
            $table->foreign('order_id')->references('id')->on('orders');
            
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

        Schema::dropIfExists('order_trackings');

        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
    }
};
