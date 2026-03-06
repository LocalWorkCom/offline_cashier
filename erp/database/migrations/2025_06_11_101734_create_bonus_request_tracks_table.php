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
        Schema::create('bonus_request_tracks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bonus_request_id');
            $table->enum('status', ['Pending', 'Approved', 'Rejected', 'Processed']);
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign keys
            $table->foreign('bonus_request_id')->references('id')->on('bonus_requests');
            $table->foreign('created_by')->references('id')->on('employees');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_request_tracks');
    }
};
