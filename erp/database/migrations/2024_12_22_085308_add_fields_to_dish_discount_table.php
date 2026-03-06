<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
   
class AddFieldsToDishDiscountTable extends Migration
{
    public function up()
    {
        Schema::table('dish_discount', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modify_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamp('deleted_at')->nullable();

            // Foreign Key Constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('modify_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('dish_discount', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['created_by']);
            $table->dropForeign(['modify_by']);
            $table->dropForeign(['deleted_by']);

            // Drop columns
            $table->dropColumn(['created_by', 'modify_by', 'deleted_by', 'deleted_at']);
            $table->dropTimestamps();
        });
    }

};
