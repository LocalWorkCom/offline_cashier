<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SupplyOrderReason;

class SupplyOrderReasonsSeeder extends Seeder
{
    public function run()
    {
        $reasons = [
            ['name_en' => 'Shortage', 'name_ar' => 'نقص'],
            ['name_en' => 'Stock Balancing', 'name_ar' => 'توازن المخزون'],
            ['name_en' => 'Project Transfer', 'name_ar' => 'تحويل مشروع'],
        ];

        foreach ($reasons as $reason) {
            SupplyOrderReason::create($reason);
        }
    }
}
