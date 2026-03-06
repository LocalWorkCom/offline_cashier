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
        Schema::table('order_details', function (Blueprint $table) {
            // First, drop the foreign key constraint for `addon_id`
            $table->dropForeign(['addon_id']);

            // Now, drop the `addon_id` column so it can be re-created as JSON
            $table->dropColumn('addon_id');
        });

        Schema::table('order_details', function (Blueprint $table) {
            // Add `addon_id` back as a JSON column
            $table->json('addon_id')->nullable(); // You can set it to nullable if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('order_details', function (Blueprint $table) {
            // Drop the JSON `addon_id` column
            $table->dropColumn('addon_id');
            
            // Restore the `addon_id` column as unsignedBigInteger
            $table->unsignedBigInteger('addon_id');

            // Re-add the foreign key constraint
            $table->foreign('addon_id')->references('id')->on('addons');
        });
    }
};
