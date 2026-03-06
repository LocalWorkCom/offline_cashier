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
        Schema::table('branch_menu_sizes', function (Blueprint $table) {
            $table->dropForeign(['branch_menu_id']);
            $table->dropColumn('branch_menu_id');

            $table->dropColumn('default_size');

            $table->unsignedBigInteger('dish_id')->nullable()->after('dish_size_id');
            $table->foreign('dish_id')->references('id')->on('dishes')->onUpdate('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_menu_sizes', function (Blueprint $table) {
            //
        });
    }
};
