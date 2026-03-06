<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MaritalStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $maritalStatuses = [
            [
                'name_ar' => 'اعزب',
                'name_en' => 'Single',	
            ],
            [
                'name_ar' => 'متزوج',
                'name_en' => 'Married',	
            ],
            [
                'name_ar' => 'مطلق',
                'name_en' => 'Divorced',	
            ],
            [
                'name_ar' => 'ارمل',
                'name_en' => 'Widowed',	
            ],
            [
                'name_ar' => 'منفصل',
                'name_en' => 'Separated',	
            ],
            [
                'name_ar' => 'مخطوب',
                'name_en' => 'Engaged',	
            ],
        ];

        DB::table('marital_statuses')->insert($maritalStatuses);
    }
}
