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
        Schema::table('zones', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->dropColumn('description');

            $table->string('name_ar');
            $table->string('name_en');
            $table->string('description_ar')->nullable();
            $table->string('description_en')->nullable();

            $table->integer('min_temperature')->nullable();
            $table->integer('max_temperature')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('created_by_type')->nullable();
            $table->unsignedBigInteger('modified_by')->nullable();
            $table->string('modified_by_type')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->string('deleted_by_type')->nullable();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zones', function (Blueprint $table) {
            //
        });
    }
};
