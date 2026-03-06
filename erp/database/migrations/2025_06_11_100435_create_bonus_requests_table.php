<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('bonus_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('employee_id');
            $table->enum('bonus_type', ['amount', 'percentage', 'days']);
            $table->text('bonus_reason')->nullable();
            $table->date('payout_date');
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Optional: Add foreign keys if the related tables exist
            $table->foreign('department_id')->references('id')->on('departments');
            $table->foreign('employee_id')->references('id')->on('employees');
            $table->foreign('created_by')->references('id')->on('employees');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bonus_requests');
    }
};
