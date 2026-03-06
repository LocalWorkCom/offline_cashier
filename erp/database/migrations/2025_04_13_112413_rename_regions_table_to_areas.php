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
         // Drop foreign keys referencing regions
         Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');

        });


        // Rename table
        Schema::rename('regions', 'areas');

        // Recreate foreign keys referencing areas
        Schema::table('employees', function (Blueprint $table) {
            $table->unsignedBigInteger('area_id')->nullable()->after('id');

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->unsignedBigInteger('area_id')->nullable()->after('id');

            $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
        });

        // Schema::table('cities', function (Blueprint $table) {
        //     $table->unsignedBigInteger('area_id')->nullable()->after('id');

        //     $table->foreign('area_id')->references('id')->on('areas')->onDelete('set null');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
