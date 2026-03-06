<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Drop the column and re-add it with the new enum values
        Schema::table('offer_details', function (Blueprint $table) {
            $table->dropColumn('offer_type');
        });

        Schema::table('offer_details', function (Blueprint $table) {
            $table->enum('offer_type', ['dishes', 'addons', 'products'])->after('offer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('offer_details', function (Blueprint $table) {
            $table->dropColumn('offer_type');
        });

        Schema::table('offer_details', function (Blueprint $table) {
            $table->enum('offer_type', ['dishes', 'categories', 'products'])->after('offer_id');
        });
    }
};
