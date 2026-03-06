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
        Schema::table('bonus_request_tracks', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('status');
            $table->date('payout_date')->nullable()->after('reason');
        });
    }

    public function down()
    {
        Schema::table('bonus_request_tracks', function (Blueprint $table) {
            $table->dropColumn(['reason', 'payout_date']);
        });
    }
};
