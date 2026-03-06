<?php

namespace Database\Seeders;

use App\Models\LeaveType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
 
class LeaveTypesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $leave_types = [
            ['name_ar' => 'إجازة اعتياديه', 'name_en' => 'Regular leave', 'created_by' =>1, 'created_at' => now(), 'image' => 'https://erpsystem.testdomain100.online/images/leave_setting/301759751891.png'],
            // ['name_ar' => 'الإجازة السنوية', 'name_en' => 'Annual Leave', 'created_by' =>1, 'created_at' => now()],
            ['name_ar' => 'إجازة مرضية', 'name_en' => 'Sick Leave', 'created_by' =>1, 'created_at' => now(), 'image' => 'https://erpsystem.testdomain100.online/images/leave_setting/301759751891.png'],
            // ['name_ar' => 'إجازة الأمومة', 'name_en' => 'Maternity Leave', 'created_by' =>1, 'created_at' => now()],
            // ['name_ar' => 'إجازة الأبوة', 'name_en' => 'Paternity Leave', 'created_by' =>1, 'created_at' => now()],
            // ['name_ar' => 'إجازة الأمومة', 'name_en' => 'Maternity Leave', 'created_by' =>1, 'created_at' => now()],
            ['name_ar' => 'العطلات الرسمية', 'name_en' => 'Public Holidays', 'created_by' =>1, 'created_at' => now(), 'image' => 'https://erpsystem.testdomain100.online/images/leave_setting/301759751891.png'],
            // ['name_ar' => 'إجازة بدون أجر', 'name_en' => 'Unpaid Leave', 'created_by' =>1, 'created_at' => now()],
            // ['name_ar' => 'إجازة الحداد', 'name_en' => 'Bereavement Leave', 'created_by' =>1, 'created_at' => now()],
            // ['name_ar' => 'إجازة دراسية', 'name_en' => 'Study Leave', 'created_by' =>1, 'created_at' => now()],
            // ['name_ar' => 'إجازة الطوارئ العائلية', 'name_en' => 'Family Emergency Leave', 'created_by' =>1, 'created_at' => now()],
            // ['name_ar' => 'إجازة الإعارة', 'name_en' => 'Secondment Leave', 'created_by' =>1, 'created_at' => now()],
            // ['name_ar' => 'إجازة طويلة الأمد', 'name_en' => 'Long-Term Leave', 'created_by' =>1, 'created_at' => now()],
        ];

        DB::table('leave_types')->insert($leave_types);
    }
}
