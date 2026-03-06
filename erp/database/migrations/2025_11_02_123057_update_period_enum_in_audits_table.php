<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change the ENUM values for 'period' column
        DB::statement("ALTER TABLE audits MODIFY COLUMN period ENUM('daily', 'weekly', 'monthly', 'once') NULL");
    }

    public function down(): void
    {
        // Revert to original ENUM values
        DB::statement("ALTER TABLE audits MODIFY COLUMN period ENUM('daily', 'weekly', 'monthly') NULL");
    }
};
