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
        Schema::table('menu', function (Blueprint $table) {
            $table->softDeletes(); 

            $table->unsignedBigInteger('created_by')->nullable(); 
            $table->foreign('created_by')->references('id')->on('users');

            $table->unsignedBigInteger('modified_by')->nullable(); 
            $table->foreign('modified_by')->references('id')->on('users');

            $table->unsignedBigInteger('deleted_by')->nullable(); 
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');

            $table->dropForeign(['modified_by']);
            $table->dropColumn('modified_by');

            $table->dropForeign(['deleted_by']);
            $table->dropColumn('deleted_by');

            $table->dropSoftDeletes(); 
        });
    }
};
