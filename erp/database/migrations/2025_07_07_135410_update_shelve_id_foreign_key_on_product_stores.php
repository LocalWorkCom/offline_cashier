<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateShelveIdForeignKeyOnProductStores extends Migration
{
    public function up()
    {
        Schema::table('product_stores', function (Blueprint $table) {
            // Drop existing foreign key constraint on shelve_id
            $table->dropForeign(['shelve_id']);

            // Optionally rename column if needed (not required here)
            // $table->renameColumn('shelve_id', 'rack_shelve_id');

            // Add new foreign key to rack_shelves table
            $table->foreign('shelve_id')->references('id')->on('rack_shelves')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table('product_stores', function (Blueprint $table) {
            // Drop new foreign key
            $table->dropForeign(['shelve_id']);

            // Recreate the old foreign key (if it was referencing shelves table, for example)
            $table->foreign('shelve_id')->references('id')->on('shelves')->onDelete('cascade');
        });
    }
}
