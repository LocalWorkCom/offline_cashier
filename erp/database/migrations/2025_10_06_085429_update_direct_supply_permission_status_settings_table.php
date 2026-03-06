<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDirectSupplyPermissionStatusSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_supply_permission_status_settings', function (Blueprint $table) {
            // First, check if the foreign key exists and drop it
            if (Schema::hasColumn('direct_supply_permission_status_settings', 'updated_by')) {
                // Drop the foreign key constraint first
                $table->dropForeign(['updated_by']);
            }
            
            // Then drop the columns
            $table->dropColumn(['updated_by', 'updated_at']);
            
            // Add soft delete
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('direct_supply_permission_status_settings', function (Blueprint $table) {
            // Reverse: remove soft delete first
            $table->dropSoftDeletes();
            
            // Add the columns back
            $table->timestamp('updated_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            
            // Re-add the foreign key constraint
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }
}