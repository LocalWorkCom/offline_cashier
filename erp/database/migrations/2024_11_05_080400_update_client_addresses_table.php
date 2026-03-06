<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('client_addresses', function (Blueprint $table) {
            $table->dropForeign(['client_details_id']);
            $table->dropColumn('client_details_id');

            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    public function down()
    {
        Schema::table('client_addresses', function (Blueprint $table) {
            // Reverse the changes
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            $table->unsignedBigInteger('client_details_id')->nullable();
            $table->foreign('client_details_id')->references('id')->on('client_details');
        });
    }
};
