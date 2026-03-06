<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingDeliversTable extends Migration
{
    public function up()
    {
        Schema::create('setting_delivers', function (Blueprint $table) {
            $table->id();
            $table->enum('vehicle_type', ['car', 'motorcycle']);
            $table->integer('vehicle_max');
            $table->integer('vehicle_min');
            $table->integer('max_order'); 
            $table->integer('min_order')->default(1); // Default 1 per person
            $table->foreignId('delivery_id')->nullable()->constrained('employees'); // Foreign key to employees table
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign keys to the users table
            $table->foreignId('created_by')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('modified_by')->nullable()->constrained('users')->cascadeOnUpdate();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->cascadeOnUpdate();
        });
    }

    public function down()
    {
        Schema::dropIfExists('setting_delivers');
    }
}

