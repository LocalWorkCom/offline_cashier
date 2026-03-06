<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change existing status column to enum with allowed values
        DB::statement("ALTER TABLE salary_advance_requests MODIFY status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Rollback (if you had an old set of enums, adjust them here)
        DB::statement("ALTER TABLE salary_advance_requests MODIFY status VARCHAR(50) NOT NULL DEFAULT 'pending'");
    }
};
