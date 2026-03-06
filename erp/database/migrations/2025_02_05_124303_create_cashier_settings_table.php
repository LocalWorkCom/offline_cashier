<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashierSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('cashier_settings', function (Blueprint $table) {
            $table->id(); // Auto-incrementing primary key
            $table->decimal('min_balance', 15, 2)->nullable(); // Minimum balance
            $table->decimal('max_balance', 15, 2)->nullable(); // Maximum balance
            $table->integer('min_count')->nullable(); // Minimum count
            $table->integer('max_count')->nullable(); // Maximum count
            $table->boolean('use_visa')->default(false); // Whether Visa is used (true/false)
            $table->time('auto_run_time')->nullable(); // Auto-run time
            $table->timestamps(); // Created and updated timestamps
            $table->softDeletes();
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cashier_settings');
    }
}
