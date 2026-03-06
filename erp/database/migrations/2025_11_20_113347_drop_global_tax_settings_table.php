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
        Schema::dropIfExists('global_tax_settings');
    }

    public function down()
    {
        Schema::create('global_tax_settings', function (Blueprint $table) {
            $table->id();
            // Add original columns only if you want rollback
            $table->timestamps();
        });
    }
};
