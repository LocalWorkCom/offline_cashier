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
        Schema::table('direct_supply_permissions', function (Blueprint $table) {
            $table->string('dsp_no')->unique()->after('id');
            $table->enum('type', ['regular', 'sudden'])
                ->comment('دوري = regular, مفاجئ = sudden')
                ->nullable()
                ->after('dsp_status_id');
        });
        Schema::rename('purchase_order_histories', 'direct_supply_permission_logs');

        // Drop the old foreign key, rename column, and re-add it
        Schema::table('direct_supply_permission_logs', function (Blueprint $table) {
            // Drop foreign key first (adjust the key name if necessary)
            $table->dropForeign(['purchase_order_id']);

            // Rename the column
            $table->renameColumn('purchase_order_id', 'dsp_id');
        });

        //  Recreate the foreign key using new column name
        Schema::table('direct_supply_permission_logs', function (Blueprint $table) {
            $table->foreign('dsp_id')
                ->references('id')
                ->on('direct_supply_permissions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
