<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert([
            [
                'name_ar' => 'العهدة',
                'name_en' => 'First Category',
                'description_ar' => 'عهدة',
                'description_en' => 'Description of the first category',
                'image' => 'images/categories/0.jpg',
                'active' => 1,
                'code' => '0000',
                'is_freeze' => 0,
                'parent_id' => null,
                'is_deleted' => 1,
                'created_by' => 1, // Assuming user ID 1 exists
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'هالك',
                'name_en' => 'Second Category',
                'description_ar' => 'هالك',
                'description_en' => 'Description of the second category',
                'image' => 'images/categories/1.jpg',
                'active' => 1,
                'code' => '0001',
                'is_freeze' => 0,
                'parent_id' => null, // Assuming this category is a child of the first one
                'is_deleted' => 1,
                'created_by' => 1, // Assuming user ID 2 exists
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'منتجات تامة',
                'name_en' => 'Second Category',
                'description_ar' => 'منتجات تامة',
                'description_en' => 'Description of the second category',
                'image' => 'images/categories/2.jpg',
                'active' => 1,
                'code' => '0002',
                'is_freeze' => 0,
                'parent_id' => null, // Assuming this category is a child of the first one
                'is_deleted' => 1,
                'created_by' => 1, // Assuming user ID 2 exists
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'منتجات',
                'name_en' => 'Second Category',
                'description_ar' => 'منتجات',
                'description_en' => 'Description of the second category',
                'image' => 'images/categories/3.jpg',
                'active' => 1,
                'code' => '0003',
                'is_freeze' => 0,
                'parent_id' => null, // Assuming this category is a child of the first one
                'is_deleted' => 1,
                'created_by' => 1, // Assuming user ID 2 exists
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'خامات',
                'name_en' => 'Second Category',
                'description_ar' => 'خامات',
                'description_en' => 'Description of the second category',
                'image' => 'images/categories/4.jpg',
                'active' => 1,
                'code' => '0004',
                'is_freeze' => 0,
                'parent_id' => null, // Assuming this category is a child of the first one
                'is_deleted' => 1,
                'created_by' => 1, // Assuming user ID 2 exists
                'deleted_by' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],


            // Add more sample categories as needed
        ]);
    }
}
