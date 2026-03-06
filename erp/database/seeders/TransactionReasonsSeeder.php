<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransactionReasonsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $data = [
            [
                'name_en' => 'Purchase',
                'name_ar' => 'شراء',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_en' => 'Return',
                'name_ar' => 'مرتجع',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_en' => 'Correction',
                'name_ar' => 'تصحيح',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_en' => 'Wastage',
                'name_ar' => 'هالك',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'name_en' => 'Kitchen Request',
                'name_ar' => 'طلب مطبخ',
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('transaction_reasons')->insert($data);
    }
}
