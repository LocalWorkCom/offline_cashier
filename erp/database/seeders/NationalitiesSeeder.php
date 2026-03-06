<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class NationalitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('nationalities')->insert([
            [
                'name_ar' => 'مصري',
                'name_en' => 'Egyptian',
            ],
            [
                'name_ar' => 'سعودي',
                'name_en' => 'Saudi',
            ],
            [
                'name_ar' => 'سوداني',
                'name_en' => 'Sudanese',
            ],
            [
                'name_ar' => 'أردني',
                'name_en' => 'Jordanian',
            ],
            [
                'name_ar' => 'عراقي',
                'name_en' => 'Iraqi',
            ],
        ]);
    }
}
