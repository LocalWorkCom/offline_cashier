<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DirectSupplyIssueTypesSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            [
                'title_en' => 'Quantity Mismatch',
                'title_ar' => 'عدم تطابق الكمية',
                'description_en' => 'Quantity Mismatch',
                'description_ar' => 'عدم تطابق الكمية',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title_en' => 'Damaged Item',
                'title_ar' => 'عنصر تالف',
                'description_en' => 'Damaged Item',
                'description_ar' => 'عنصر تالف',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'title_en' => 'Missing Item',
                'title_ar' => 'عنصر مفقود',
                'description_en' => 'Missing Item',
                'description_ar' => 'عنصر مفقود',
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        DB::table('direct_supply_issue_types')->insert($data);
    }
}
