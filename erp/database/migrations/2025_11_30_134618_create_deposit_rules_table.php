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
        Schema::create('deposit_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Deposit Name
            $table->unsignedTinyInteger('percentage'); // 1 - 100
            $table->enum('applicable_to', ['all', 'restricted'])->default('all'); // Vendor scope
            $table->enum('vendor_type', ['individual', 'company', 'local_market'])->nullable(); // optional
            $table->text('conditions')->nullable(); // JSON field for custom rules e.g., PO>50000
            $table->boolean('active')->default(1); // active/deactivated
            $table->foreignId('linked_high_value_rule_id')->nullable()->constrained('high_value_rules')->nullOnDelete(); // optional link
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposit_rules');
    }
};
