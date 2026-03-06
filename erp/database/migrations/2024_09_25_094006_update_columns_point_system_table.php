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
        Schema::table('point_systems', function (Blueprint $table) {
            $table->dropColumn('name');

            // Add 'name_ar' and 'name_en' columns as strings
            $table->string('name_ar')->nullable()->after('value');
            $table->string('name_en')->nullable()->after('name_ar');

            // Modify the 'key' column instead of adding it again
            $table->enum('key', ['0', '1'])->nullable()->comment('0->currency , 1-> percentage')->change();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('point_systems', function (Blueprint $table) {
            $table->enum('name', ['byOrder', 'byProduct'])->nullable()->after('value');

            // Drop the 'name_ar' and 'name_en' columns
            $table->dropColumn('name_ar');
            $table->dropColumn('name_en');

            // Revert the 'key' column to its previous state
            $table->enum('key', ['percentage', 'currency'])->nullable()->change();

    });
    }
};
