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
        Schema::table('cashier_settings', function (Blueprint $table) {
            // Adding the foreign key column for `pos_id`
            $table->unsignedBigInteger('pos_id')->nullable()->after('id');
            $table->unsignedBigInteger('employee_id')->nullable()->after('pos_id');

            // Adding foreign key constraints
            $table->foreign('pos_id')->references('id')->on('cashier_machines')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cashier_settings', function (Blueprint $table) {
            // Drop foreign key constraints first
            $table->dropForeign(['pos_id']);
            $table->dropForeign(['employee_id']);

            // Drop the columns
            $table->dropColumn('pos_id');
            $table->dropColumn('employee_id');
        });
    }
};
