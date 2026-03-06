<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
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

        Schema::table('leave_settings', function (Blueprint $table) {
            //$table->dropPrimary();
        });

        Schema::table('leave_types', function (Blueprint $table) {
            //$table->dropPrimary();
        });

        //DB::table('leave_types')->truncate();

        Schema::table('leave_types', function (Blueprint $table) {
            //$table->unsignedBigInteger('id')->primary()->change();
        });
        

        DB::statement('ALTER TABLE leave_types MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;');

        Schema::table('leave_settings', function (Blueprint $table) {
            //$table->unsignedBigInteger('id')->primary()->change();
            $table->unsignedBigInteger('leave_type_id')->nullable()->change();
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onUpdate('cascade');
        });

        DB::statement('ALTER TABLE leave_settings MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_types', function (Blueprint $table) {
            //
        });
    }
};