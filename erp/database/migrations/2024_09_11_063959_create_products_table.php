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
        Schema::dropIfExists('products'); // Drop the table if it already exists
        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('main_image')->nullable();
            $table->enum('type', ['complete', 'raw']);
            $table->unsignedBigInteger('main_unit_id');
            // $table->decimal('price', 10, 2); //10.00
            $table->string('currency_code');
            $table->unsignedBigInteger('category_id');
            $table->boolean('is_valid')->default(1);
            $table->string('sku')->unique();
            $table->string('barcode')->unique();
            $table->string('code')->unique();
            $table->boolean('is_remind')->default(1);
            // $table->integer('limit_quantity')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modify_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modify_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('main_unit_id')->references('id')->on('units');
            // $table->foreign('currency_code')->references('id')->on('countries');
            $table->foreign('category_id')->references('id')->on('categories');
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

        Schema::dropIfExists('products');

        Schema::enableForeignKeyConstraints(); // Re-enable foreign key checks
    }
};
