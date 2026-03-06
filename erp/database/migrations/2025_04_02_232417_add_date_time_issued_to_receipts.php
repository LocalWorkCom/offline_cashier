<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDateTimeIssuedToReceipts extends Migration
{
    public function up()
    {
        Schema::table('einvoices', function (Blueprint $table) {
            $table->timestamp('dateTimeIssued')->nullable();  // Adds dateTimeIssued column
        });
    }

    public function down()
    {
        Schema::table('einvoices', function (Blueprint $table) {
            $table->dropColumn('dateTimeIssued');  // Drops the column if rolling back
        });
    }
}
