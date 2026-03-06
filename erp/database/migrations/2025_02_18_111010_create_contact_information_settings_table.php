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
        Schema::create('contact_information_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('company_profile_settings')->onDelete('cascade');
            $table->string('company_address');
            $table->string('map_link')->nullable();
            $table->string('phone_number')->unique();
            $table->string('email')->unique();
            $table->string('website_link')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
           
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();        });
    }

    public function down()
    {
        Schema::dropIfExists('contact_information_settings');
    }
};
