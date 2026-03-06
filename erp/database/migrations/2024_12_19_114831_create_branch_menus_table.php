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
        Schema::create('branch_menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('dish_id')->nullable();
            $table->unsignedBigInteger('dish_category_id')->nullable();
            $table->unsignedBigInteger('branch_menu_category_id')->nullable();
            $table->unsignedBigInteger('cuisine_id')->nullable();
            $table->integer('is_active')->default(0)->nullable();
            $table->integer('has_size')->default(0)->nullable();
            $table->integer('has_addon')->default(0)->nullable();
            $table->decimal('price', 8,2)->default(0)->nullable();
            $table->unsignedBigInteger('created_by')->default(10);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade');
            $table->foreign('dish_id')->references('id')->on('dishes')->onUpdate('cascade');
            $table->foreign('dish_category_id')->references('id')->on('dish_categories')->onUpdate('cascade');
            $table->foreign('branch_menu_category_id')->references('id')->on('branch_menu_categories')->onUpdate('cascade');
            $table->foreign('cuisine_id')->references('id')->on('cuisines')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');
        });

        Schema::table('branch_menu_sizes', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_menu_id')->nullable();
            $table->foreign('branch_menu_id')->references('id')->on('branch_menu_sizes')->onUpdate('cascade');
        });

        Schema::table('branch_menu_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_menu_id')->nullable();
            $table->foreign('branch_menu_id')->references('id')->on('branch_menu_addons')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branch_menus');
    }
};
