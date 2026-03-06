<?php

namespace Database\Seeders;

use App\Models\StorageLocation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StorageLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        StorageLocation::insert([
            [
                'name_en' => 'Freezer',
                'name_ar' => 'الفريزر',
                'description_en' => 'For frozen items',
                'description_ar' => 'للعناصر المجمدة'
            ],
            [
                'name_en' => 'Fridge',
                'name_ar' => 'الثلاجة',
                'description_en' => 'For refrigerated items',
                'description_ar' => 'للعناصر المبردة'
            ],
            [
                'name_en' => 'Dry Store',
                'name_ar' => 'المخزن الجاف',
                'description_en' => 'For dry goods storage',
                'description_ar' => 'لتخزين البضائع الجافة'
            ],
            [
                'name_en' => 'Ambient Storage',
                'name_ar' => 'التخزين في درجة حرارة الغرفة',
                'description_en' => 'For room temperature storage',
                'description_ar' => 'للتخزين في درجة حرارة الغرفة'
            ]
        ]);
    }
}