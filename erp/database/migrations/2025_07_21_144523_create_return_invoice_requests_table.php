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
        Schema::create('return_invoice_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->json('invoice_details_ids')->nullable();
            $table->text('resone')->nullable();
            $table->text('reject_resone')->nullable();
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->enum('status', ['pending', 'accept', 'reject'])->default('pending')->nullable();
            $table->string('request_num')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onUpdate('cascade');
            $table->foreign('approved_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('created_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('modified_by')->references('id')->on('employees')->onUpdate('cascade');
            $table->foreign('deleted_by')->references('id')->on('employees')->onUpdate('cascade');    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('return_invoice_requests');
    }
};
