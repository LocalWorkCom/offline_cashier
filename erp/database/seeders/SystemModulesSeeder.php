<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SystemModulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['name' => 'HR', 'is_active' => true],
            ['name' => 'Inventory', 'is_active' => true],
            ['name' => 'Finance', 'is_active' => true],
            ['name' => 'Purchases', 'is_active' => true],
            ['name' => 'Restaurant', 'is_active' => true],
        ];

        DB::table('system_modules')->insert($modules);
    }
}
