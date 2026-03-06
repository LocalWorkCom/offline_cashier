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
        Schema::create('leave_request_agreements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_request_id')->nullable();
            $table->unsignedBigInteger('agreement_by')->nullable();
            $table->date('date')->nullable();
            $table->string('resone')->nullable();
            $table->integer('agreement')->default(1)->nullable()->comment('1 not agree,2 agree');
            $table->integer('created_by')->nullable();
            $table->integer('modified_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->timestamps();

            $table->foreign('leave_request_id')->references('id')->on('leave_requests')->onUpdate('cascade');
            $table->foreign('agreement_by')->references('id')->on('employees')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_request_agreements');
    }
};
