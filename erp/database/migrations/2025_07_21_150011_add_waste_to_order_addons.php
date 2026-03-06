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
        Schema::table('order_addons', function (Blueprint $table) {
            $table->enum('waste', ['wasted', 'temp', 'not_wasted'])->default('not_wasted')->nullable();
            $table->enum('status', ['pending', 'completed', 'cancel', 'inprogress', 'hold'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            //
        });
    }
};
