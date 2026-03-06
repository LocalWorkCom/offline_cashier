<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\City;
use App\Models\Country;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable foreign key checks if necessary

        // Truncate the table to avoid duplication

        // Fetching country ID for Egypt using phone code +20
        $country_id = Country::where('phone_code', '+20')->value('id');
        $city_id = City::where('country_id',$country_id)->first()->value('id');
        $area_id = Area::where('city_id', $city_id )->first()->value('id');
        // Employee data with flags
        $employees = [
            // Flag: Waiter
            [
                'first_name' => 'Ahmed',
                'last_name' => 'El-Sayed',
                'email' => 'ahmed.waiter@example.com',
                'country_id' => $country_id,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'country_code' => '+20',
                'phone_number' => '01021345678',
                'employee_code' => 'E001',
                'flag' => 'waiter',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Flag: Chef
            [
                'first_name' => 'Mohamed',
                'last_name' => 'Fawzy',
                'email' => 'mohamed.chef@example.com',
                'country_id' => $country_id,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'country_code' => '+20',
                'phone_number' => '01123547892',
                'employee_code' => 'E002',
                'flag' => 'chef',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Flag: Cashier
            [
                'first_name' => 'Sara',
                'last_name' => 'Ali',
                'email' => 'sara.cashier@example.com',
                'country_id' => $country_id,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'country_code' => '+20',
                'phone_number' => '01234567890',
                'employee_code' => 'E003',
                'flag' => 'cashier',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Flag: Call Center
            [
                'first_name' => 'Hassan',
                'last_name' => 'Rashid',
                'email' => 'hassan.callcenter@example.com',
                'country_id' => $country_id,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'country_code' => '+20',
                'phone_number' => '01098765432',
                'employee_code' => 'E004',
                'flag' => 'customer_service',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Flag: Head Chef
            [
                'first_name' => 'Fatima',
                'last_name' => 'Hassan',
                'email' => 'fatima.headchef@example.com',
                'country_id' => $country_id,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'country_code' => '+20',
                'phone_number' => '01145678901',
                'employee_code' => 'E005',
                'flag' => 'Head Chef',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Flag: Customer Service
            [
                'first_name' => 'Mona',
                'last_name' => 'Said',
                'email' => 'mona.customerservice@example.com',
                'country_id' => $country_id,
                'area_id' => $area_id,
                'city_id' => $city_id,
                'country_code' => '+20',
                'phone_number' => '01056378901',
                'employee_code' => 'E006',
                'flag' => 'customer_service',
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        // Insert the employees data into the database
        DB::table('employees')->insert($employees);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable foreign key checks
    }
}
