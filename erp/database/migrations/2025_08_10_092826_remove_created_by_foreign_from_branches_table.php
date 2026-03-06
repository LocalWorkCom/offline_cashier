<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('branches', function (Blueprint $table) {
            // First, drop the foreign key constraint
            $table->dropForeign(['created_by']);
            
            // Optionally, you can keep the column but remove the foreign key
            // Or if you want to remove the column completely, uncomment:
            // $table->dropColumn('created_by');
        });
    }

    public function down()
    {
        Schema::table('branches', function (Blueprint $table) {
            // For rollback, you would recreate the foreign key
            // You'll need to know the original reference table
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            
            // Or if you dropped the column, recreate it:
            // $table->unsignedBigInteger('created_by')->nullable();
            // $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }
};