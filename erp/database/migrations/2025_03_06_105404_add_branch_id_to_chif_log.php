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
        Schema::table('chif_manager_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('employee_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('chif_manager_logs', function (Blueprint $table) {
            //
        });
    }
};
