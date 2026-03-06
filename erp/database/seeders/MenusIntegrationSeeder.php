<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenusIntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus_integrations')->insert([
            [
                'name_ar' => 'طلبات',
                'name_en' => 'Talabat',
                'is_active' => 1,
                'created_at' => now(),
            ]
        ]);
    }
}
