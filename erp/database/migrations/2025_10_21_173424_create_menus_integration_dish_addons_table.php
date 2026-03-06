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
        Schema::create('menus_integration_dish_addons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('menus_integration_id')->nullable();
            $table->unsignedBigInteger('branch_menu_id')->nullable();
            $table->unsignedBigInteger('branch_menu_addon_id')->nullable();
            $table->decimal('price', 10,2)->nullable()->default(0);
            $table->boolean('is_active')->nullable()->default(1);
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();            
            
            //relations
            $table->foreign('menus_integration_id')->references('id')->on('menus_integrations')->onUpdate('cascade');
            $table->foreign('branch_menu_id')->references('id')->on('branch_menus')->onUpdate('cascade');
            $table->foreign('branch_menu_addon_id')->references('id')->on('branch_menu_addons')->onUpdate('cascade');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus_integration_dish_addons');
    }
};
