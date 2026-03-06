<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('reject_reasons', 'supply_order_reject_reasons');
    }

    public function down(): void
    {
        Schema::rename('supply_order_reject_reasons', 'reject_reasons');
    }
};
