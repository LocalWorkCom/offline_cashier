<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEinvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('einvoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();
            $table->uuid('uuid');
            $table->enum('status', ['pending', 'submitted', 'rejected', 'cancelled'])->default('pending');
            $table->json('public_urls')->nullable(); // Stores multiple URLs as JSON
            $table->date('cancel_request_date')->nullable();
            $table->date('reject_request_date')->nullable();
            $table->date('submission_date')->nullable();
            $table->text('validation_ar')->nullable();
            $table->text('validation_en')->nullable();
            $table->text('error_msg')->nullable();
            $table->enum('invoice_type', ['i', 'c'])->comment('i: Invoice, c: Credit Note');
            $table->unsignedBigInteger('reference_invoice_id')->nullable();
            $table->date('expired_on')->nullable();
            $table->text('reject_reason')->nullable();
            $table->text('cancel_reason')->nullable();

            // Define foreign key for reference invoice if needed
            $table->foreign('reference_invoice_id')->references('id')->on('einvoices')->onDelete('set null');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('einvoices');
    }
}
