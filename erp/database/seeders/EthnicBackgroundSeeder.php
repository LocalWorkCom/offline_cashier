<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EthnicBackgroundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('ethnic_backgrounds')->insert([
            [
                'name_ar' => 'مسلم',
                'name_en' => 'Muslim',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
            // ,
            // [
            //     'name_ar' => 'مسيحي',
            //     'name_en' => 'Christian',
            //     'created_by' => 1,
            //     'created_at' => now(),
            //     'updated_at' => now(),
            // ]
        ]);
    }
}
