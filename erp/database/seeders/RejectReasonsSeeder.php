<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RejectReason;

class RejectReasonsSeeder extends Seeder
{
    public function run()
    {
        $reasons = [
            [
                'name_en' => 'Incomplete data',
                'name_ar' => 'بيانات غير مكتملة',
            ],
            [
                'name_en' => 'Unauthorized request',
                'name_ar' => 'طلب غير موجود',
            ],
            [
                'name_en' => 'Mismatch in warehouse selection',
                'name_ar' => 'اختلاف في اختيار المخزن',
            ],
            [
                'name_en' => 'Duplicate order',
                'name_ar' => 'طلب مكرر',
            ],
            [
                'name_en' => 'Invalid item quantity',
                'name_ar' => 'كمية عنصر غير صالحة',
            ],
        ];

        foreach ($reasons as $reason) {
            RejectReason::create($reason);
        }
    }
}
