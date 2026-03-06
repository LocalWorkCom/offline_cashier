<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('Delivery', 'CallCenter', 'Takeaway', 'Online', 'dine-in') NOT NULL");
    }

    public function down()
    {
        // Revert the enum values in case of rollback
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('OldValue1', 'OldValue2') NOT NULL");
    }
};
