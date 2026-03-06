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
        Schema::table('offers', function (Blueprint $table) {
            $table->enum('discount_type',['fixed','percentage'])->nullable();
            $table->integer('discount_value')->nullable();
            $table->string('branch_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('offers', function (Blueprint $table) {
            $table->dropColumn('discount_type');
            $table->dropColumn('discount_value');
            $table->dropColumn('branch_id');
        });
    }
};
