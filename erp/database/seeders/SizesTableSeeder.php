<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizesTableSeeder extends Seeder
{
    public function run()
    {
        $sizes = [
            // Clothes Sizes (category_id = 1)
            ['name_ar' => 'صغير جدا', 'name_en' => 'XS', 'category_id' => 1],
            ['name_ar' => 'صغير', 'name_en' => 'S', 'category_id' => 1],
            ['name_ar' => 'متوسط', 'name_en' => 'M', 'category_id' => 1],
            ['name_ar' => 'كبير', 'name_en' => 'L', 'category_id' => 1],
            ['name_ar' => 'كبير جدا', 'name_en' => 'XL', 'category_id' => 1],
            ['name_ar' => 'كبير جدا جدا', 'name_en' => 'XXL', 'category_id' => 1],

            // Shoes Sizes (category_id = 1)
            ['name_ar' => '36', 'name_en' => '36', 'category_id' => 1],
            ['name_ar' => '37', 'name_en' => '37', 'category_id' => 1],
            ['name_ar' => '38', 'name_en' => '38', 'category_id' => 1],
            ['name_ar' => '39', 'name_en' => '39', 'category_id' => 1],
            ['name_ar' => '40', 'name_en' => '40', 'category_id' => 1],
            ['name_ar' => '41', 'name_en' => '41', 'category_id' => 1],
            ['name_ar' => '41', 'name_en' => '41', 'category_id' => 1],
            ['name_ar' => '43', 'name_en' => '43', 'category_id' => 1],
            ['name_ar' => '44', 'name_en' => '44', 'category_id' => 1],
            ['name_ar' => '45', 'name_en' => '45', 'category_id' => 1],
        ];

        DB::table('sizes')->insert($sizes);
    }
}
