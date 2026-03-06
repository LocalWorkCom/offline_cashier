<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('branch_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('no_show_fee', 10, 2)->default(0);
            $table->decimal('deposit_deduction_percentage', 5, 2)->default(0);
            $table->integer('alert_before_arrival_minutes')->default(0);
            $table->integer('alert_after_arrival_minutes')->default(0);
            $table->enum('refund_policy', ['full', 'partial', 'none'])->default('none');
            $table->decimal('refund_percentage', 5, 2)->default(0);
            $table->integer('auto_cancel_time_limit')->default(0);
            $table->integer('table_session_minutes')->default(0);
            $table->integer('takeaway_session_minutes')->default(0);
            $table->tinyInteger('takeaway_deposit_value_type')->default(0); // 0 or 1 delete that
            $table->decimal('takeaway_deposit_value_if_1', 10, 2)->nullable(); //based on payment poiliciy
            $table->tinyInteger('table_cancelation_time_type')->default(0); // 0 or 1 delete that
            $table->integer('table_cancelation_time_if_1')->nullable(); // not based
            $table->tinyInteger('table_cancelation_value_type')->default(0); // 0 or 1 // spare in payment poiliciy
            $table->decimal('table_cancelation_value_if_1', 10, 2)->nullable(); //based on payment poiliciy
            $table->integer('capacity_takeaway')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branch_settings');
    }
};
