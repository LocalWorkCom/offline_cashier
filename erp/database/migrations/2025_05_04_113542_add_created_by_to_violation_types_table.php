<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreatedByToViolationTypesTable extends Migration
{
    public function up()
    {
        Schema::table('violation_types', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->after('id');

            // Optional: Add foreign key constraint if you have a users table
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('violation_types', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
        });
    }
}
