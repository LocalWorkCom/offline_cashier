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
        Schema::table('branch_menus', function (Blueprint $table) {
            $table->dropForeign(['dish_category_id']);
            $table->dropColumn('dish_category_id');

            $table->dropForeign(['cuisine_id']);
            $table->dropColumn('cuisine_id');

            $table->dropColumn('has_size');
            $table->dropColumn('has_addon');
            
            $table->integer('is_product')->default(0)->nullable()->after('is_active');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_menus', function (Blueprint $table) {
            //
        });
    }
};
