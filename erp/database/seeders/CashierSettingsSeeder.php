<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CashierSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'min_balance' => 100.00,
                'max_balance' => 5000.00,
                'min_count' => 10,
                'max_count' => 500,
                'auto_run_time' => '10:00:00',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
                'deleted_at' => null,
                'created_by' => 1,
                'modified_by' => null,
                'deleted_by' => null,
            ],
        ];

        DB::table('cashier_settings')->insert($settings);
    }
}
