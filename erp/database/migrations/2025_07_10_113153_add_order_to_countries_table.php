<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            if (!Schema::hasColumn('countries', 'order')) {
                Schema::table('countries', function (Blueprint $table) {
                    $table->integer('order')->default(0)->after('flag');
                });
            }

            // Update existing null values to default
            DB::table('countries')
                ->whereNull('order')
                ->update(['order' => 0]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            //
        });
    }
};
