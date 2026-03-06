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
        Schema::table('zones', function (Blueprint $table) {
            // Drop the existing foreign key first
            $table->dropForeign(['warehouse_id']);

            // Then add the new foreign key to `stores` table
            $table->foreign('warehouse_id')->references('id')->on('stores')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('zones', function (Blueprint $table) {
            // Rollback: drop the new relation
            $table->dropForeign(['warehouse_id']);

            // Restore the old relation to `warehouses` table
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('cascade');
        });
    }
};
