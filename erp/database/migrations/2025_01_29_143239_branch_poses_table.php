<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('branch_poses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained();
            $table->string('posserial')->unique();
            $table->string('pososversion');
            $table->string('posmodelframework');
            $table->string('presharedkey');
            $table->string('vendor');
            $table->string('name');
            $table->text('description')->nullable();
            $table->date('credential_expired')->nullable();
            $table->boolean('ready_to_submit')->default(false);
            $table->date('active_from')->nullable();
            $table->date('active_to')->nullable();
            $table->enum('status', ['active', 'not_active'])->default('not_active');
            $table->date('first_authentication')->nullable();
            $table->date('last_authentication')->nullable();
            $table->date('last_sent_receipt')->nullable();
            $table->date('retirement_date')->nullable();
            $table->date('permanent_retirement_date')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('branch_poses');
    }
};
