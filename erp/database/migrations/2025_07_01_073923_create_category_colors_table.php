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
        Schema::create('category_colors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('color_id');

            // Foreign key constraints
            $table->foreign('category_id')
                  ->references('id')
                  ->on('categories')
                  ->onDelete('cascade');

            $table->foreign('color_id')
                  ->references('id')
                  ->on('colors')
                  ->onDelete('cascade');

            // Composite unique index to prevent duplicate relationships
            $table->unique(['category_id', 'color_id']);
            $table->unsignedBigInteger('created_by')->default(1);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();

            $table->timestamps();
            $table->foreign('created_by')->references('id')->on('employees');
            $table->foreign('modified_by')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('deleted_by')->references('id')->on('employees')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('category_colors');
    }
};