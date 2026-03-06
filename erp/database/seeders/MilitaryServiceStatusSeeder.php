<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MilitaryServiceStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $militaryServiceStatuses = [
            [
                'name_ar' => 'مطلوب',
                'name_en' => 'Required',
            ],
            [
                'name_ar' => 'معفى',
                'name_en' => 'Exempted',
            ],
            [
                'name_ar' => 'مؤجل',
                'name_en' => 'Postponed',
            ],
            [
                'name_ar' => 'مؤدي',
                'name_en' => 'Completed',
            ],
            [
                'name_ar' => 'غير مطلوب',
                'name_en' => 'Not Required',
            ],
            [
                'name_ar' => 'لا ينطبق',
                'name_en' => 'Not Applicable',
            ],
        ];

        DB::table('military_service_statuses')->insert($militaryServiceStatuses);
    }
}
