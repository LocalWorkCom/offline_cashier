<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('cashier_machine_id')->nullable()->after('id');
            
            $table->foreign('cashier_machine_id')
                  ->references('id')
                  ->on('cashier_machines')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cashier_machine_id']);
            $table->dropColumn('cashier_machine_id');
        });
    }
};
