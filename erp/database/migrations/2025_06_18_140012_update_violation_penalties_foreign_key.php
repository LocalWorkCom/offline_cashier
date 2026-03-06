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
        if (Schema::hasColumn('violation_penalties', 'penalty_id')) {
            Schema::table('violation_penalties', function (Blueprint $table) {
                $table->dropForeign(['penalty_id']);
                $table->dropColumn('penalty_id');
            });
        }

        // Now safely add the column and constraint
        Schema::table('violation_penalties', function (Blueprint $table) {
            if (!Schema::hasColumn('violation_penalties', 'penalty_id')) {
                $table->foreignId('penalty_id')
                    ->after('violation_id')
                    ->constrained('penalty_reasons')
                    ->onDelete('cascade');
            }
        });

        
    }

    public function down()
    {
        Schema::table('violation_penalties', function (Blueprint $table) {
            $table->dropForeign(['penalty_id']);
            $table->dropColumn('penalty_id');

            $table->foreignId('penalty_id')->constrained('penalties')->onDelete('cascade');
        });
    }
};
