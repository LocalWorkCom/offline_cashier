<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PoliciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('policies_payment_reservation')->insert([
            [
                'payment_ar' => 'الدفع نقدًا أو عبر البطاقة',
                'payment_en' => 'Payment in cash or by card',
                'reservation_ar' => 'يجب الحجز قبل يومين',
                'reservation_en' => 'Reservation must be made two days in advance',
                'created_by' => 1, // Assuming user ID 1 exists
                'updated_by' => 1, // Assuming user ID 1 exists
                'created_at' => now(),
                'updated_at' => now(),
            ],

        ]);
    }
}
