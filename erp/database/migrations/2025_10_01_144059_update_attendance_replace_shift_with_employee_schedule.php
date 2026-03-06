<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Drop shift_id if exists
            if (Schema::hasColumn('attendances', 'shift_id')) {
                $table->dropForeign(['shift_id']); // if FK exists
                $table->dropColumn('shift_id');
            }

            // Add employee_schedule_id
            $table->unsignedBigInteger('employee_schedule_id')->nullable()->after('employee_id');

            $table->foreign('employee_schedule_id')
                ->references('id')->on('employee_schedules')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Rollback: drop employee_schedule_id
            $table->dropForeign(['employee_schedule_id']);
            $table->dropColumn('employee_schedule_id');

            // Restore shift_id
            $table->unsignedBigInteger('shift_id')->nullable()->after('employee_id');
            $table->foreign('shift_id')
                ->references('id')->on('shifts')
                ->onDelete('set null');
        });
    }
};
