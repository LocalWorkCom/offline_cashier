<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\Employee;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class EmployeeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
   public function run()
{
    $flags = [
        'hr', 'hr', 'finance', 'finance', 'inventory', 'inventory', 'admin', 'employee'
    ];

    foreach ($flags as $index => $flag) {
        $i = $index + 1;
        Employee::updateOrCreate(
            ['employee_code' => 'EMP' . str_pad($i, 3, '0', STR_PAD_LEFT)],
            [
                'first_name' => ucfirst($flag) . 'First' . $i,
                'last_name' => ucfirst($flag) . 'Last' . $i,
                'email' => $flag . $i . '@example.com',
                'country_code' => '+965',
                'phone_number' => '6000000' . $i,
                'flag' => $flag,
                'gender' => 'male',
                'birth_date' => now()->subYears(25)->format('Y-m-d'),
                'status' => 'active',
                'daily_excuse_hours' => 0.00,
                'monthly_excuse_hours' => 0.00,
                'created_by' => 1,
                'password' => Hash::make('123456'),
            ]
        );
    }
}

}
