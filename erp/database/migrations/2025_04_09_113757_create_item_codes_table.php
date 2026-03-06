<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemCodesTable extends Migration
{
    public function up()
    {
        Schema::create('item_codes', function (Blueprint $table) {
            $table->id();
            $table->string('codeType')->default('EGS');
            $table->string('parentCode')->default('10000051');
            $table->string('itemCode')->unique();
            $table->string('codeName');
            $table->string('codeNameAr');
            $table->timestamp('activeFrom')->nullable();
            $table->timestamp('activeTo')->nullable();
            $table->text('description')->nullable();
            $table->text('descriptionAr')->nullable();
            $table->text('requestReason')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('item_codes');
    }
}
