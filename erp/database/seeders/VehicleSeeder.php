<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\VehicleSetting;

class VehicleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        VehicleSetting::create([
            'vehicle_type' => VehicleSetting::VEHICLE_TYPE_CAR,
            'vehicle_max' => 8,
            'vehicle_min' => 4,
            'created_by' => 1, // Ensure user with ID 1 exists
            'created_at' => now(),
        ]);

        VehicleSetting::create([
            'vehicle_type' => VehicleSetting::VEHICLE_TYPE_MOTORCYCLE,
            'vehicle_max' => 4,
            'vehicle_min' => 1,
            'created_by' => 1, // Ensure user with ID 1 exists
            'created_at' => now(),

        ]);
    }
}
