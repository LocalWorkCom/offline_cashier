<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('employee_status')->insert([
            [
                'name_ar' => 'نشط',
                'name_en' => 'Active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'متدرب',
                'name_en' => 'Trainee',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'مُنهى',
                'name_en' => 'Terminated',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'في إجازة',
                'name_en' => 'On Leave',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'مُوقوف عن العمل',
                'name_en' => 'Suspended',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'مستقيل',
                'name_en' => 'Resigned',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name_ar' => 'غير نشط',
                'name_en' => 'Not Active',
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
