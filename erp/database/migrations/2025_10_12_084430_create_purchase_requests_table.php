<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->id();

            $table->string('pr_number')->unique();
            $table->unsignedBigInteger('store_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'rejected'])->default('draft');
            $table->dateTime('pr_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->unsignedBigInteger('reason_pr_id');

            $table->unsignedBigInteger('created_by');
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('approved_by_type')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->string('rejected_by_type')->nullable();
            $table->unsignedBigInteger('submitted_by')->nullable();
            $table->string('submitted_by_type')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('submitted_at')->nullable();
            

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('store_id')->references('id')->on('stores')->onDelete('restrict');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->foreign('reason_pr_id')->references('id')->on('reason_purchase_requests')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requests');
    }
};
