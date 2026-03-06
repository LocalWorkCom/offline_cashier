<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE employees MODIFY COLUMN flag ENUM(
            'officer','waiter','chef','cashier','call_center','customer_service',
            'driver','kitchen manager','branch manager','kitchen staff',
            'supervisor','employee','Head Board','Head Chef','hr'
        )");
    }

    public function down()
    {
        DB::statement("ALTER TABLE employees MODIFY COLUMN flag ENUM(
            'officer','waiter','chef','cashier','call_center','customer_service',
            'driver','kitchen manager','branch manager','kitchen staff',
            'supervisor','employee','Head Board','Head Chef'
        )");
    }
};
