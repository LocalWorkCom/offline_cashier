<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->string('phone_code', 10)->nullable(); // Add phone code column
            $table->integer('length')->nullable()->after('phone_code'); // Add phone length column
        });
    }

    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['phone_code', 'length']);
        });
    }
};
