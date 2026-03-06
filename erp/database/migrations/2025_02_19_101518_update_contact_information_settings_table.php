<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Rename 'company_address' to 'company_address_ar'
        Schema::table('contact_information_settings', function (Blueprint $table) {
            $table->renameColumn('company_address', 'company_address_ar');
        });

        // Add 'company_address_en' column
        Schema::table('contact_information_settings', function (Blueprint $table) {
            $table->string('company_address_en')->after('company_address_ar');
        });
    }

    public function down(): void
    {
        // Reverse the changes
        Schema::table('contact_information_settings', function (Blueprint $table) {
            $table->dropColumn('company_address_en');
        });

        Schema::table('contact_information_settings', function (Blueprint $table) {
            $table->renameColumn('company_address_ar', 'company_address');
        });
    }
};
