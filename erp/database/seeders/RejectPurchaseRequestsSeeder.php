<?php

namespace Database\Seeders;

use App\Models\RejectPurchaseRequest;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RejectPurchaseRequestsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         $reasons = [
            [
                'name_ar'   => 'قيود الميزانية',
                'name_en'   => 'Budget Constraints',
                'is_active' => 1,
            ],
            [
                'name_ar'   => 'الصنف متوفر بالفعل في المخزون',
                'name_en'   => 'Item Already Available in Stock',
                'is_active' => 1,
            ],
            [
                'name_ar'   => 'طلب غير صحيح أو مكرر',
                'name_en'   => 'Incorrect or Duplicate Request',
                'is_active' => 1,
            ],
            [
                'name_ar'   => 'غير متوافق مع سياسة الشركة',
                'name_en'   => 'Not Aligned with Company Policy',
                'is_active' => 1,
            ],
            [
                'name_ar'   => 'مبررات غير كافية',
                'name_en'   => 'Insufficient Justification Provided',
                'is_active' => 1,
            ],
        ];

        foreach ($reasons as $reason) {
            RejectPurchaseRequest::updateOrCreate(
                ['name_en' => $reason['name_en']], 
                $reason
            );
        }
    }
}
