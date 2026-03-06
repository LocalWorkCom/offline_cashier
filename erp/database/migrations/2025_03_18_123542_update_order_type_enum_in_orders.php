<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('Delivery', 'CallCenter', 'Takeaway', 'Online', 'dine-in', 'reservation-table') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders MODIFY COLUMN type ENUM('Delivery', 'CallCenter', 'Takeaway', 'Online', 'dine-in') NOT NULL");
    }
};
