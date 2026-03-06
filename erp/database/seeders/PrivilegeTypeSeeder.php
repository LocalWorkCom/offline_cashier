<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrivilegeTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name_en' => 'Company Car', 'name_ar' => 'سيارة الشركة', 'is_active' => true],
            ['name_en' => 'Club Membership', 'name_ar' => 'عضوية النادي', 'is_active' => true],
            ['name_en' => 'Meal Allowance', 'name_ar' => 'بدل وجبة', 'is_active' => true],
        ];


        DB::table('privilege_types')->insert($data);
    }
}
