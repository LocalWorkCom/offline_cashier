<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SalarySystemTypesSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('salary_system_types_settings')->insert([
            ['name' => 'Daily Salary', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Weekly Salary', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Monthly Salary', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Mixed Salary', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
