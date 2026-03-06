<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;


class UpdatePreparationAppointmentEnumInOrdersTable extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE orders MODIFY preparation_appointment ENUM('at', 'before') NOT NULL");
    }

    public function down(): void
    {
        // Optional: revert to previous ENUM values if known, or set a default fallback
        DB::statement("ALTER TABLE orders MODIFY preparation_appointment ENUM('after', 'before') NOT NULL");
    }
}
