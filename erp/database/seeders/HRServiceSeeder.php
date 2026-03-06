<?php
// database/seeders/HRServiceSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\HRService;

class HRServiceSeeder extends Seeder
{
    public function run()
    {
        HRService::create([
            'key' => 'LeaveRequest',
            'name_en' => 'Leave Request',
            'name_ar' => 'طلب اجازه',
            'description_ar' => 'طلب لاخذ اجازه من العمل',
            'description_en' => 'Request to take time off from work.',
        ]);

        HRService::create([
            'key' => 'SalaryAdvance',
            'name_en' => 'Salary Advance',
            'name_ar' => 'سلفه من الراتب',
            'description_en' => 'Request for an advance on salary.',
            'description_ar' => 'طلب سلفه من الراتب',
        ]);
    }
}
