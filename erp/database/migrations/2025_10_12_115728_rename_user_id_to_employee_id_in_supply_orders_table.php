<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('supply_orders', function (Blueprint $table) {
            // Drop old foreign key first
            $table->dropForeign(['user_id']);

            // Rename column
            $table->renameColumn('user_id', 'employee_id');
        });

        Schema::table('supply_orders', function (Blueprint $table) {
            // Add new foreign key referencing employees
            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
              ;
        });
    }

    public function down()
    {
        Schema::table('supply_orders', function (Blueprint $table) {
            // Drop the new foreign key
            $table->dropForeign(['employee_id']);

            // Rename back to user_id
            $table->renameColumn('employee_id', 'user_id');
        });

        Schema::table('supply_orders', function (Blueprint $table) {
            // Restore old foreign key to users table
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }
};
