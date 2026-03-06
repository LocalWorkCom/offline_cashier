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
        Schema::create('point_systems', function (Blueprint $table) {
            //this table to define points and the method of calculating points based on the invoice or product and the method of calculating by percentage or currency and the value of calculating the point

            $table->id();
            $table->enum('name', ['byOrder', 'byProduct'])->nullable();
            $table->enum('key', ['percentage', 'currency'])->nullable();
            $table->string('value');
            $table->integer('active')->comment('0 -> not active , 1 -> active')->default(0);

            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();


            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modified_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('point_systems');
    }
};
