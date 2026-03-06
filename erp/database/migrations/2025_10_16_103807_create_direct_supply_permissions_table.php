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
        Schema::create('direct_supply_permissions', function (Blueprint $table) {
            $table->id();
            $table->timestamp('date')->useCurrent();
            $table->foreignId('from_store_id')->constrained('stores');
            $table->foreignId('to_store_id')->constrained('stores');
            $table->string('department')->default('Inventory');
            $table->foreignId('linked_pr_id')->nullable()->constrained('purchase_requests');
            $table->foreignId('linked_so_id')->nullable()->constrained('supply_orders');
            $table->foreignId('qa_tester_id')->nullable()->constrained('employees');
            $table->foreignId('dsp_status_id')->nullable()->constrained('direct_supply_permission_status_settings');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
