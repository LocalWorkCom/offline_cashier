<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('company_profile_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->string('business_activity');
            $table->string('logo')->nullable(); // Store the file path
            $table->string('trade_license');
            $table->date('license_expiry_date');
            $table->string('tax_registration_number');
            $table->string('capital')->nullable(); // Capital stored as a string
            $table->string('scanned_trade_license')->nullable(); // Store the file path
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
           
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('company_profile_settings');
    }
};
