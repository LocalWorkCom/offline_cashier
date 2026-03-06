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
        Schema::table('purchase_order_statuses', function (Blueprint $table) {
            $table->dropColumn('steps');

            $table->integer('parent_id')->nullable();
            $table->integer('child_id')->nullable();
            $table->boolean('type')->default(0);
            $table->integer('base_status')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_order_statuses', function (Blueprint $table) {
            $table->string('steps')->nullable();
            $table->dropColumn(['parent_id', 'child_id', 'base_status']);
        });
    }
};
