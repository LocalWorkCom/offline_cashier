<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddClientAddressIdToOrdersTable extends Migration
{
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('client_address_id')->nullable(); // Add client_address_id column

            // If you want to add a foreign key constraint to the 'client_address_id' column, use:
            $table->foreign('client_address_id')->references('id')->on('client_addresses');
        });
    }

    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('client_address_id'); // Drop the column if the migration is rolled back
        });
    }
}
