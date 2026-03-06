<?php

namespace Database\Seeders;

use App\Models\DocumentNameType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentNameTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'code' => 'PR',
                'name_en' => 'Purchase Request',
                'name_ar' => 'طلب شراء',
            ],
            [
                'code' => 'PO',
                'name_en' => 'Purchase Order',
                'name_ar' => 'أمر شراء',
            ],
            [
                'code' => 'SO',
                'name_en' => 'Supplier Order',
                'name_ar' => 'أمر توريد',
            ],
            [
                'code' => 'INV',
                'name_en' => 'Invoice',
                'name_ar' => 'فاتورة',
            ],
            [
                'code' => 'PD',
                'name_en' => 'Pricing Deal',
                'name_ar' => 'عرض أسعار',
            ],
            [
                'code' => 'PAY',
                'name_en' => 'Payment',
                'name_ar' => 'دفعة',
            ],
        ];

        foreach ($types as $type) {
            DocumentNameType::updateOrCreate(
                ['code' => $type['code']],
                [
                    'name_en' => $type['name_en'],
                    'name_ar' => $type['name_ar'],
                    'created_by' => 1, // Optional: set default admin if needed
                ]
            );
        }
    }
}
