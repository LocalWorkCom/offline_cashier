<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategoryShipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {


        DB::table('categories')->insert([
            'name_ar' => 'شحن',
            'name_en' => 'shipment',
            'description_ar' => 'شحن',
            'description_en' => 'shipment',
            'image' => null,
            'active' => 1,
            'code' => '0050',
            'is_freeze' => 0,
            'parent_id' => null,
            'is_deleted' => 0,
            'created_by' => 1, 
            'deleted_by' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Category shipment types seeded successfully!');
    }
}
