<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hr_services', function (Blueprint $table) {
            $table->id();
            $table->string('key'); // key of the service Camel Case   (e.g., 'LeaveRequest', 'SalaryAdvance')
            $table->string('name_ar'); // Name of the service (e.g., 'Leave Request', 'Salary Advance')
            $table->string('name_en'); // Name of the service (e.g., 'Leave Request', 'Salary Advance')
            $table->string('description_ar')->nullable(); // Optional description of the service
            $table->string('description_en')->nullable(); // Optional description of the service
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('updated_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('hr_services');
    }
};
