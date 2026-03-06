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
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->dropColumn('no_show_fee');
            $table->dropColumn('deposit_deduction_percentage');
            $table->dropColumn('refund_policy');
            $table->dropColumn('refund_percentage');
            $table->dropColumn('table_cancelation_value_if_1');
            $table->dropColumn('auto_cancel_time_limit');
            $table->enum('deposit_without_order_deduction_policy', ['none', 'full', 'part'])->default('none')->after('branch_id');
            $table->decimal('deposit_without_order_deduction_percentage', 5, 2)->default(0)->after('deposit_without_order_deduction_policy');
            $table->enum('deposit_with_order_deduction_policy', ['none', 'full', 'part'])->default('none')->after('deposit_without_order_deduction_percentage');
            $table->decimal('deposit_with_order_deduction_percentage', 5, 2)->default(0)->after('deposit_with_order_deduction_policy');
            $table->enum('full_paid_order_deduction_policy', ['none', 'full', 'part'])->default('none')->after('deposit_with_order_deduction_percentage');
            $table->decimal('full_paid_order_deduction_percentage', 5, 2)->default(0)->after('full_paid_order_deduction_policy');
            $table->renameColumn('table_cancelation_time_if_1', 'table_cancelation_time_allowed')->comment('(in minutes) for both user and auto cancellation')->after('full_paid_order_deduction_percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_settings', function (Blueprint $table) {
            $table->decimal('no_show_fee', 10, 2)->default(0)->after('branch_id');
            $table->decimal('deposit_deduction_percentage', 5, 2)->default(0)->after('no_show_fee');
            $table->enum('refund_policy', ['full', 'partial', 'none'])->default('none')->after('deposit_deduction_percentage');
            $table->decimal('refund_percentage', 5, 2)->default(0)->after('refund_policy');
            $table->decimal('table_cancelation_value_if_1', 10, 2)->nullable()->after('refund_percentage');

            $table->dropColumn('deposit_without_order_deduction_policy');
            $table->dropColumn('deposit_without_order_deduction_percentage');
            $table->dropColumn('deposit_with_order_deduction_policy');
            $table->dropColumn('deposit_with_order_deduction_percentage');
            $table->dropColumn('full_paid_order_deduction_policy');
            $table->dropColumn('full_paid_order_deduction_percentage');

            $table->renameColumn('table_cancelation_time_allowed', 'table_cancelation_time_if_1');
        });
    }
};
