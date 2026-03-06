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
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->renameColumn('min_leave', 'day_count');
            $table->renameColumn('max_leave', 'day_paid');
            $table->integer('day_unpaid')->nullable()->default(0);
            $table->enum('leave_type', ['day', 'hour'])->nullable()->default('day');
            $table->integer('within_month')->nullable()->default(0);
            $table->enum('upload_certificate', ['yes', 'no'])->nullable()->default('no');
            $table->enum('transfer_leave', ['money', 'next_year', 'nothing'])->nullable()->default('nothing');
            $table->string('file')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_settings', function (Blueprint $table) {
            //
        });
    }
};
