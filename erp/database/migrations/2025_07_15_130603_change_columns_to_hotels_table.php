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
        Schema::table('hotels', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('address_en');
            $table->integer('building_number')->nullable()->after('phone_number');
            $table->string('branch_name_en')->nullable()->after('building_number');
            $table->string('branch_name_ar')->nullable()->after('branch_name_en');
            $table->boolean('status')->default(true)->after('branch_name_ar');
            $table->string('note')->nullable()->after('status');
            $table->integer('shiping_cost')->nullable()->after('note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotels', function (Blueprint $table) {
            $table->dropColumn(['phone_number', 'building_number', 'branch_name', 'status']);
        });
    }
};
