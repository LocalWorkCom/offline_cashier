<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->unsignedBigInteger('storage_location_id')->nullable()->after('store_id');

            // If you have a storage_locations table and want a foreign key:
            $table->foreign('storage_location_id')
                ->references('id')
                ->on('storage_locations');
                
        });
    }

    public function down(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->dropForeign(['storage_location_id']);
            $table->dropColumn('storage_location_id');
        });
    }
};
