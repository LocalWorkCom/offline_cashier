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
            $table->dropPrimary();
            $table->uuid('id')->primary()->unique()->index()->change();
            // $table->char('country_id', 36)->nullable()->change();
            // $table->foreign('country_id')->references('id')->on('countries')->onUpdate('cascade');
            // $table->char('leave_type_id', 36)->change();
            // $table->foreign('leave_type_id')->references('id')->on('leave_types')->onUpdate('cascade');
            // $table->char('created_by', 36)->nullable()->change();
            // $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            // $table->char('modified_by', 36)->nullable()->change();
            // $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            // $table->char('deleted_by', 36)->nullable()->change();
            // $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_settings', function (Blueprint $table) {
            $table->id()->change();
        });
    }
};
