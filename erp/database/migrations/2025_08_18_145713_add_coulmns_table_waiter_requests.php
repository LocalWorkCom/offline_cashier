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
       Schema::table('waiter_requests', function (Blueprint $table) {
            // Make user_id nullable
            $table->unsignedBigInteger('user_id')->nullable()->change();

            // Add branch_id (FK to branches)
            $table->unsignedBigInteger('branch_id')->nullable()->after('user_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');

            // Add employee_id (FK to employees)
            $table->unsignedBigInteger('employee_id')->nullable()->after('branch_id');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('waiter_requests', function (Blueprint $table) {
            //
        });
    }
};
