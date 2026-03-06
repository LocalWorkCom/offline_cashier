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
        Schema::create('waste_report_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_report_id')->constrained('waste_reports')->onDelete('cascade');

            $table->foreignId('product_brand_id')->constrained('product_brands')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('waste_reason_id')->constrained('waste_reasons')->onDelete('cascade');
            $table->foreignId('waste_unit_id')->nullable()->constrained('units')->onDelete('set null');
            $table->decimal('quantity', 10, 2)->default(0);
            $table->decimal('actual_quantity_wasted', 10, 2)->default(0);
            $table->enum('status', ['pending', 'reviewed'])->default('pending');
            $table->unsignedBigInteger('reviewed_employee_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('reviewed_employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('deleted_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_report_items');
    }
};
