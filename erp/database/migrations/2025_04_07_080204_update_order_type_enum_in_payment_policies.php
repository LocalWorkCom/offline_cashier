<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Change enum values using raw SQL (MySQL only)
        DB::statement("ALTER TABLE payment_policies MODIFY order_type ENUM('takeaway', 'delivery', 'dine-in', 'reservation_with_order', 'reservation_without_order') NOT NULL");
    }

    public function down(): void
    {
        // Rollback to previous enum values
        DB::statement("ALTER TABLE payment_policies MODIFY order_type ENUM('takeaway', 'delivery', 'dine-in') NOT NULL");
    }
};

