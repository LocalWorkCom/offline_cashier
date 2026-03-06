<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up()
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'completed', 'cancelled', 'cart', 'open') NOT NULL");
    }

    public function down()
    {
        // Revert the enum values in case of rollback
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('OldValue1', 'OldValue2') NOT NULL");
    }
};
