<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('social_media_informations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')
                ->constrained('company_profile_settings')
                ->onDelete('cascade');
            $table->string('android_app_link')->nullable();
            $table->string('ios_app_link')->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('snapchat')->nullable();
            $table->string('twitter')->nullable();
            $table->string('tiktok')->nullable();
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
        Schema::dropIfExists('social_media_informations');
    }
};

