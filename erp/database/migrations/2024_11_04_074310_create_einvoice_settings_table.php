<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class CreateEinvoiceSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('einvoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed the initial settings values if desired
        DB::table('einvoice_settings')->insert([
            ['key' => 'company_name', 'value' => ''],
            ['key' => 'tax_issuer_id', 'value' => ''],
            ['key' => 'tax_activity', 'value' => ''],
            ['key' => 'tax_item_code_type', 'value' => ''],
            ['key' => 'idSrvBaseUrlPreprodEg', 'value' => ''],
            ['key' => 'idSrvBaseUrlProdEg', 'value' => ''],
            ['key' => 'apiBaseUrlPreprodEg', 'value' => ''],
            ['key' => 'apiBaseUrlProdEg', 'value' => ''],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('einvoice_settings');
    }
}
