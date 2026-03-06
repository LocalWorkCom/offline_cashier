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
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('purchase_order_issues');
        Schema::dropIfExists('purchase_order_statuses');
        Schema::dropIfExists('purchase_order_reasons');
        Schema::dropIfExists('purchase_orders');

        Schema::enableForeignKeyConstraints();
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();

            // Basic Info
            $table->string('po_number')->unique();
            $table->enum('type_po', ['pr_linked', 'unlinked']);

            // Foreign Keys
            $table->unsignedBigInteger('pr_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('to_department_id')->nullable();

            // Address
            $table->string('address')->nullable();
            $table->string('lat')->nullable();
            $table->string('long')->nullable();

            // PO Type
            $table->enum('type', ['direct', 'indirect'])->nullable();

            // Priority
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Low');

            // Dates
            $table->date('arraival_date')->nullable();
            $table->integer('period')->nullable();
            $table->decimal('total', 15, 2)->nullable();

            // Notes
            $table->text('note_delivery')->nullable();
            $table->text('note')->nullable();

            // PM Approval
            $table->unsignedBigInteger('pm_approval_id')->nullable();
            $table->timestamp('pm_approval_at')->nullable();

            // FM Approval
            $table->unsignedBigInteger('fm_approval_id')->nullable();
            $table->timestamp('fm_approval_at')->nullable();

            // Status
            $table->enum('status', [
                'draft',
                'submitted',
                'accept_po',
                'accept_fm',
                'accepted',
                'rejected',
            ])->default('draft');

            // Rejection flow
            $table->timestamp('rejected_at')->nullable();
            $table->enum('rejected_from', ['fm', 'pm'])->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();

            $table->foreign('pr_id')->references('id')->on('purchase_requests')->nullOnDelete();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('employee_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('to_department_id')->references('id')->on('departments')->nullOnDelete();
            $table->foreign('pm_approval_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('fm_approval_id')->references('id')->on('employees')->nullOnDelete();
            $table->foreign('rejected_by')->references('id')->on('employees')->nullOnDelete();
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
