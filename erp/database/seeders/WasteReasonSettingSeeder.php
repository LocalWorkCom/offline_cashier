<?php

namespace Database\Seeders;

use App\Models\WasteReason;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WasteReasonSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::table('waste_reasons')->truncate();

        $wasteReasons = [
            [
                'name_ar' => 'منتهي الصلاحية',
                'name_en' => 'Expired',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'ظروف تخزين سيئة',
                'name_en' => 'Poor Storage Conditions',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'خطأ في التسليم',
                'name_en' => 'Delivery Mistake',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ];

        WasteReason::insert($wasteReasons);
    }
}
