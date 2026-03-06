<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddModifiedAtToDirectSupplyPermissionStatusSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('direct_supply_permission_status_settings', function (Blueprint $table) {
            // Add modified_at timestamp
            $table->timestamp('modified_at')->nullable();
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
            // Remove modified_at
            $table->dropColumn('modified_at');
        });
    }
}