<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VehicleSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('vehicle_settings')->insert([
            'vehicle_type' => 'motorcycle',
            'vehicle_max' => 4,
            'vehicle_min' => 1,
        ]);
        DB::table('vehicle_settings')->insert([
            'vehicle_type' => 'car',
            'vehicle_max' => 4,
            'vehicle_min' => 1,
        ]);
    }
}
