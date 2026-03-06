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
        Schema::create('hr_requests', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->foreignId('employee_id')->constrained('employees'); // Foreign key to hr_services table
            $table->foreignId('hr_service_id')->constrained('hr_services')->onDelete('cascade'); // Foreign key to hr_services table
            $table->integer('request_id'); // ID of either leave or salary advance request
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Status of the request
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('updated_by_type')->nullable();

            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();

            $table->timestamps(); // Created at & Updated at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hr_requests');
    }
};
