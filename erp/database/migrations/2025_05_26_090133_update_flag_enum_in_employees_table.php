<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::table('employees', function (Blueprint $table) {
            $table->enum('flag', [
                'officer', 'waiter', 'chef', 'cashier', 'call_center', 'customer_service',
                'driver', 'kitchen manager', 'branch manager', 'kitchen staff',
                'supervisor', 'employee', 'Head Board', 'Head Chef', 'hr', 'admin'
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            //
        });
    }
};
