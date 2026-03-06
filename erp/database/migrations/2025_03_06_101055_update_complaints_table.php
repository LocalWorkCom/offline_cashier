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
        Schema::table('complaints', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('users')->cascadeOnUpdate();
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnUpdate();
            $table->enum('status', ['pending', 'inprogress', 'solved'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('complaints', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['order_id']);
            $table->enum('status', ['inprogress', 'solved'])->default('inprogress')->change();
        });
    }
};
