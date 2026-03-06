<?php

namespace Database\Seeders;

use App\Models\ReasonPurchaseRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReasonPurchaseRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            [
                'name_en' => 'Shortage of Stock',
                'name_ar' => 'نقص في المخزون',
            ],
            [
                'name_en' => 'Special Needs / One-time Requirement',
                'name_ar' => 'احتياجات خاصة / طلب لمرة واحدة',
            ],
            [
                'name_en' => 'Seasonal Demand',
                'name_ar' => 'الطلب الموسمي',
            ],
            [
                'name_en' => 'Damaged / Expired Stock Replacement',
                'name_ar' => 'استبدال مخزون تالف / منتهي الصلاحية',
            ],
            [
                'name_en' => 'Expansion / New Project Requirement',
                'name_ar' => 'متطلبات توسع / مشروع جديد',
            ],
            [
                'name_en' => 'Promotional or Marketing Activities',
                'name_ar' => 'أنشطة ترويجية أو تسويقية',
            ],
            [
                'name_en' => 'Operational / Maintenance Requirement',
                'name_ar' => 'متطلبات تشغيل / صيانة',
                
            ],
        ];

        foreach ($reasons as $reason) {
            ReasonPurchaseRequest::create([
                'name_en' => $reason['name_en'],
                'name_ar' => $reason['name_ar'],
                'is_active' => 1,
            ]);
        }
    }
}
