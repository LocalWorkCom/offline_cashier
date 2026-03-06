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
        Schema::table('menu', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->change();
            $table->unsignedBigInteger('dish_id')->nullable()->change();
            $table->unsignedBigInteger('cuisine_id')->nullable()->change();
            $table->decimal('price', 8,2)->nullable()->change();
            $table->integer('has_size')->nullable()->change();
            $table->integer('has_addon')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            //
        });
    }
};
