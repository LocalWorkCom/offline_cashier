<?php

namespace Database\Seeders;

use App\Models\TypeAlert;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeAlertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TypeAlert::insert([
            ['name' => 'Late arrivals'],
            ['name' => 'Absences'],
            ['name' => 'Missed check-ins'],
            ['name' => 'Overtime approvals'],
        ]);
    }
}
