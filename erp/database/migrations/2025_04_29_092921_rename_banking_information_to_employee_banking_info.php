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
        Schema::rename('banking_information', 'employee_banking_info');
    }

    public function down()
    {
        Schema::rename('employee_banking_info', 'banking_information');
    }

};
