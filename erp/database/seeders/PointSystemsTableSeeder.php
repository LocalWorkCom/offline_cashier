<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PointSystemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {  $system = [
        // Clothes Sizes (category_id = 1)
        ['type_ar' => 'بأجمالى الفاتوره', 'type_en' => 'total of order value', 'value_earn' => '1', 'point_redeem' =>'1', 'active' => 1,
        'created_at' => now(),
        'updated_at' => now()],
         ];


    DB::table('point_systems')->insert($system);

    }
}
