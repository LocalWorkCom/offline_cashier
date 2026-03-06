<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusColumnToOrderAddonsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->enum('status', ['cancel', 'completed'])->default('completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_addons', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
}
