<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeReasonNullableInCancellationReasonsTable extends Migration
{
    public function up()
    {
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            $table->text('reason')->nullable()->change();
        });
    }

    public function down()
    {
        Schema::table('cancellation_reasons', function (Blueprint $table) {
            $table->text('reason')->nullable(false)->change();
        });
    }
}
