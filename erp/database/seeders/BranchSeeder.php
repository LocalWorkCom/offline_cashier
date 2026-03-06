<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $country_id = Country::where('phone_code', '+20')->value('id');
        $city_id = City::where('country_id',$country_id)->first()->value('id');
        $area_id = Area::where('city_id', $city_id )->first()->value('id');

        $branches = [
            [
                'name_en' => 'Branch 1',
                'name_ar' => 'الفرع 1',
                'address_en' => '123 Main St, City 1',
                'address_ar' => 'شارع 123، المدينة 1',
                'latitute' => '29.3759',
                'longitute' => '47.9774',
                'country_id' => $country_id,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'phone' => '+965 12345678',
                'email' => 'branch1@example.com',
                'manager_name' => 'John Doe',
                'opening_hour' => '08:00:00',
                'closing_hour' => '20:00:00',
                'has_kids_area' => 1,
                'created_by' => 1, // Replace with actual user ID
                'modified_by' => 1, // Replace with actual user ID
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'is_delivery' => 1,
                'employee_id' => 3, // Replace with actual employee ID
                'is_default' => 1,
                'tax_application' => 1,
                'coupon_application' => 1,
                'tax_percentage' => 5.00,
                'time_cancellation' => 15,
                'delivery_time' => 30,
                'service_fees' => 2.50,
                'tax_apply' => 1,
                'is_active' => 1,
                'delivery_fees' => 5.00,
                'governate' => 'Al Kuwait',
                'regionCity' => 'Al Asimah',
                'street' => 'Main Street',
                'buildingNumber' => '10',
                'postalCode' => '12345',
                'floor' => '2',
                'room' => '301',
                'landmark' => 'Near the park',
                'additionalInformation' => 'Operating 7 days a week.',
                'service_fees_type' => 'fixed',
                'business_activity_id' => 1,
                'company_profile_setting_id' => 1,
                'is_table_reservation' => 1,
                'is_takeaway' => 1,
                'code' => 'BR001',
                'auto_close_chat' => 15,
                'is_live' => 1,
            ],
        ];

        // Insert the branches data
        DB::table('branches')->insert($branches);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
