<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('table_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('table_id')->nullable();
            $table->unsignedBigInteger('client_id')->nullable();
            $table->date('date')->nullable();
            $table->time('time_from')->nullable();
            $table->time('time_to')->nullable();
            $table->integer('status')->default(1)->comment('1 for pendding, 2 for existing, 3 for empty');
            $table->integer('confirmed')->default(1)->comment('1 for pendding, 2 for confirm, 3 for reject');
            $table->date('confirmed_date')->nullable();
            $table->time('confirmed_time')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();

            $table->unsignedBigInteger('created_by')->default(10);
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('table_id')->references('id')->on('tables')->onUpdate('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('confirmed_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_reservations');
    }
};
