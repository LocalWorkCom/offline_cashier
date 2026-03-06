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
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();

           
            $table->string('name_ar'); 
            $table->string('name_en')->nullable(); 

            $table->time('on_duty_time');
            $table->time('off_duty_time');
            $table->time('start_sign_in');
            $table->time('end_sign_in');
            $table->time('start_sign_out');
            $table->time('end_sign_out');
            $table->integer('lateness_grace_period')->default(0);
            $table->enum('start_late_time_option', [
                'after_duty_time_grace_period',
                'after_duty_time',
                'from_duty_time'
            ])->default('after_duty_time_grace_period');
            
          
            $table->unsignedBigInteger('created_by');
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->softDeletes();
            $table->timestamps();

       
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('modified_by')->references('id')->on('users');
            $table->foreign('deleted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};
