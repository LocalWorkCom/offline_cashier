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
        Schema::table('branch_menus', function (Blueprint $table) {
            $table->boolean('is_menus_integration')->nullable()->default(0)->after('price');
            $table->json('menus_integration_ids')->nullable()->after('is_menus_integration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_menus', function (Blueprint $table) {
            //
        });
    }
};
