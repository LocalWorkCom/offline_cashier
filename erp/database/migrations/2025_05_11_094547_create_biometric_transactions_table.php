<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('biometric_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('emp_code');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('department')->nullable();
            $table->string('position')->nullable();
            $table->timestamp('punch_time')->nullable();
            $table->string('punch_state')->nullable(); // raw state like "0"
            $table->string('punch_state_display')->nullable(); // e.g., "Check In"
            $table->integer('verify_type')->nullable();
            $table->string('verify_type_display')->nullable(); // e.g., "Password"
            $table->string('work_code')->nullable();
            $table->string('gps_location')->nullable();
            $table->string('area_alias')->nullable();
            $table->string('terminal_sn')->nullable();
            $table->decimal('temperature', 5, 2)->nullable();
            $table->string('terminal_alias')->nullable();
            $table->timestamp('upload_time')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_transactions');
    }
};
