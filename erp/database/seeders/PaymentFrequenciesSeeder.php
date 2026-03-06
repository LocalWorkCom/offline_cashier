<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentFrequenciesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        DB::table('payment_frequencies')->insert([
            ['name_en' => 'monthly',   'name_ar' => 'شهري',      'created_at' => $now, 'updated_at' => $now, 'payment_weekday' => null, 'payment_monthday' => 20],
            ['name_en' => 'weekly',    'name_ar' => 'أسبوعي',    'created_at' => $now, 'updated_at' => $now, 'payment_weekday' => 1, 'payment_monthday' => null],
            ['name_en' => 'daily',     'name_ar' => 'يومي',      'created_at' => $now, 'updated_at' => $now, 'payment_weekday' => null, 'payment_monthday' => null],

        ]);
    }
}
