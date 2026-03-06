<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToEinvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('einvoices', function (Blueprint $table) {
            $table->uuid('old_uuid')->nullable();
            $table->bigInteger('long_id')->nullable();
            $table->uuid('previous_uuid')->nullable();
            $table->boolean('has_return_receipts')->default(false);
        });

    
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('einvoices', function (Blueprint $table) {
            $table->dropColumn(['old_uuid', 'long_id', 'previous_uuid', 'has_return_receipts']);
        });
    }
}
