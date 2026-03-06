<?php

namespace Database\Seeders;

use App\Models\DiscrepancyReason;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiscrepancyReasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $discrepancy_types = [
            ['name_en' => 'Permanent Waste', 'name_ar' => 'هدر دائم'],
            ['name_en' => 'Loss', 'name_ar' => 'فقدان'],
            ['name_en' => 'Administrative Mistake', 'name_ar' => 'خطأ إداري'],
            ['name_en' => 'Transfer Error', 'name_ar' => 'خطأ في التحويل'],
            ['name_en' => 'Unrecorded Usage', 'name_ar' => 'استخدام غير مسجل'],
        ];

        foreach ($discrepancy_types as $discrepancy_type) {
            DiscrepancyReason::create($discrepancy_type);
        }
    }
}
