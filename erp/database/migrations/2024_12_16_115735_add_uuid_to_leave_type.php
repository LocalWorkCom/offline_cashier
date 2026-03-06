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
            $table->dropForeign(['leave_type_id']);
        }); 
        
        Schema::table('leave_nationals', function (Blueprint $table) {
            $table->dropForeign(['leave_type_id']);
        }); 

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropForeign(['leave_type_id']);
        }); 

        Schema::table('leave_types', function (Blueprint $table) {
            $table->dropPrimary();
            $table->uuid('id')->primary()->unique()->index()->change();
        });

        Schema::table('leave_settings', function (Blueprint $table) {
            $table->char('leave_type_id', 36)->nullable()->change();
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onUpdate('cascade');
        }); 

        Schema::table('leave_nationals', function (Blueprint $table) {
            $table->char('leave_type_id', 36)->nullable()->change();
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onUpdate('cascade');
        }); 

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->char('leave_type_id', 36)->nullable()->change();
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onUpdate('cascade');
        }); 

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            $table->id()->change();
        });
    }
};
