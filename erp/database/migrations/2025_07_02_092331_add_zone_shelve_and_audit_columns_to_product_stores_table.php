<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddZoneShelveAndAuditColumnsToProductStoresTable extends Migration
{
    public function up(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
         
            $table->foreign('zone_id')->references('id')->on('zones');
            $table->foreign('shelve_id')->references('id')->on('shelves');
      
        });
    }

    public function down(): void
    {
        Schema::table('product_stores', function (Blueprint $table) {
            $table->dropForeign(['zone_id']);
            $table->dropForeign(['shelve_id']);
 

            $table->dropColumn([
                'zone_id',
                'shelve_id',
            
            ]);
        });
    }
}
