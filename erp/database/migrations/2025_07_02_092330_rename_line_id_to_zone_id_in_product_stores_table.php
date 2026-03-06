<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameLineIdToZoneIdInProductStoresTable extends Migration
{
    public function up(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->renameColumn('line_id', 'zone_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->renameColumn('zone_id', 'line_id');
        });
    }
}
