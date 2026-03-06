<?php

namespace Database\Seeders;

use App\Models\TableDropdown;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TableDropdownSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tables = [
            // ['name' => 'areas', 'source_of_table' => 'areas'],
            // ['name' => 'branches', 'source_of_table' => 'branches'],
            // ['name' => 'categories', 'source_of_table' => 'categories'],
            // ['name' => 'cities', 'source_of_table' => 'cities'],
            ['name' => 'colors', 'source_of_table' => 'colors'],
            // ['name' => 'countries', 'source_of_table' => 'countries'],
            // ['name' => 'departments', 'source_of_table' => 'departments'],
            // ['name' => 'dishes', 'source_of_table' => 'dishes'],
            // ['name' => 'floors', 'source_of_table' => 'floors'],
            // ['name' => 'logos', 'source_of_table' => 'logos'],
            // ['name' => 'nationalities', 'source_of_table' => 'nationalities'],
            // ['name' => 'offers', 'source_of_table' => 'offers'],
            // ['name' => 'orders', 'source_of_table' => 'orders'],
            // ['name' => 'products', 'source_of_table' => 'products'],
            // ['name' => 'shelves', 'source_of_table' => 'shelves'],
            ['name' => 'sizes', 'source_of_table' => 'sizes'],
            // ['name' => 'vehicle_settings', 'source_of_table' => 'vehicle_settings'],
        ];

        foreach ($tables as $table) {
            TableDropdown::insert([
                'name' => $table['name'],
                'source_of_table' => $table['source_of_table'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
