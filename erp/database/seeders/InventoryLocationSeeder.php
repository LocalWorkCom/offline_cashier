<?php

namespace Database\Seeders;

use App\Models\InventoryLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InventoryLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $audit_types = [
            ['name_en' => 'Full', 'name_ar' => 'كلي'],
            ['name_en' => 'Partial', 'name_ar' => 'جزئى'],
            ['name_en' => 'Location-Based', 'name_ar' => 'منطقه'],
        ];

        foreach ($audit_types as $audit_type) {
            InventoryLocation::create($audit_type);
        }
    }
}
