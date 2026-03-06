<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EmployeeImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $employees = Employee::whereNull('image')->get();

        foreach ($employees as $employee) {
            $defaultImage = $employee->gender === 'female' ? 'female.png' : 'male.png';
            $imagePath = "images/employees/$defaultImage";
            $fullImageUrl = url($imagePath);
            $employee->update(['image' => $fullImageUrl]);
        }
        echo "Employee images updated successfully.\n";
    }
}
