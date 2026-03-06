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
        Schema::create('leave_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('leave_type_id')->nullable();
            $table->date('date')->nullable();
            $table->date('from')->nullable();
            $table->date('to')->nullable();
            $table->integer('leave_count')->nullable();
            $table->string('resone')->nullable();
            $table->integer('stauts')->default(1)->nullable()->comment('1 not yet,2 current,3 exceed');
            $table->integer('agreement')->default(1)->nullable()->comment('1 not agree,2 agree');
            $table->unsignedBigInteger('agreement_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('leave_type_id')->references('id')->on('leave_types')->onUpdate('cascade');
            $table->foreign('agreement_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('users')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('users')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_requests');
    }
};
