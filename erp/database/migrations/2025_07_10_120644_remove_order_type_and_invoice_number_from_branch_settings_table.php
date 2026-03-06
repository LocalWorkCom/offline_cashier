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
        Schema::table('branch_settings', function (Blueprint $table) {
            // Drop the columns
            $table->dropColumn('order_type');
            $table->dropColumn('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            // Add the columns back if we need to rollback
            $table->string('order_type')->nullable()->after('branch_id');
            $table->integer('invoice_number')->default(0)->after('order_type');
        });
    }
};