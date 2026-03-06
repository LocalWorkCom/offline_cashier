<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanyProfileSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable foreign key checks if necessary

        // Truncate the table to avoid duplication
        DB::table('company_profile_settings')->truncate();

        // Insert sample records
        DB::table('company_profile_settings')->insert([
            [
                'name_ar' => 'شركة مثال',
                'name_en' => 'Example Company',
                'description_ar' => 'وصف الشركة باللغة العربية',
                'description_en' => 'Description of the company in English',
                'business_activity' => 'Retail',
                'logo' => 'example_logo.png',
                'trade_license' => 'TL123456',
                'license_expiry_date' => Carbon::now()->addYears(1), // 1 year from now
                'tax_registration_number' => 'TRN123456789',
                'capital' => 1000000.00, // 1 million
                'scanned_trade_license' => 'scanned_license_image.png',
                'created_by' => 1, // assuming user with ID 1 created it
                'modified_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name_ar' => 'شركة جديدة',
                'name_en' => 'New Company',
                'description_ar' => 'وصف الشركة الجديدة باللغة العربية',
                'description_en' => 'Description of the new company in English',
                'business_activity' => 'Construction',
                'logo' => 'new_company_logo.png',
                'trade_license' => 'TL987654',
                'license_expiry_date' => Carbon::now()->addYears(2), // 2 years from now
                'tax_registration_number' => 'TRN987654321',
                'capital' => 5000000.00, // 5 million
                'scanned_trade_license' => 'scanned_new_company_license.png',
                'created_by' => 1, // assuming user with ID 1 created it
                'modified_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable foreign key checks
    }
}
