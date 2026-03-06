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
        Schema::rename('purchase_order_issue_types', 'direct_supply_issue_types');
        Schema::table('direct_supply_issue_types', function (Blueprint $table) {
            $table->text('description_ar')->nullable()->after('title_en');
            $table->text('description_en')->nullable()->after('description_ar');
            $table->foreignId('created_by')->nullable()->constrained('employees')->onDelete('set null')->after('status');
            $table->foreignId('updated_by')->nullable()->constrained('employees')->onDelete('set null')->after('status');
            $table->foreignId('deleted_by')->nullable()->constrained('employees')->onDelete('set null')->after('status');

            $table->unique('title_ar');
            $table->unique('title_en');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('direct_supply_issue_types', 'purchase_order_issue_types');
    }
};
