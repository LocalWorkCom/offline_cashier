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
  Schema::table('payment_policy_invoice_counts', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_policy_invoice_counts', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
            }

            if (!Schema::hasColumn('payment_policy_invoice_counts', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }

            if (!Schema::hasColumn('payment_policy_invoice_counts', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            }
            // Add type columns (string or enum)
            $table->string('created_by_type')->nullable()->after('created_by');
            $table->string('updated_by_type')->nullable()->after('updated_by');
            $table->string('deleted_by_type')->nullable()->after('deleted_by');
        });    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
