<?php
namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EmployeeImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {  
        Log::info('Excel File Rows', ['rows' => $rows->toArray()]);

        foreach ($rows as $row) {
            // Log each row separately for debugging
            Log::info('Processing Employee Row', ['row' => $row]);

            // Ensure employee_code is not null before processing
            if (!isset($row['employee_code']) || empty($row['employee_code'])) {
                Log::error('Skipping Row - Missing Employee Code', ['row' => $row]);
                continue; // Skip this row
            }

            // Ensure auth user is available
            $createdBy = Auth::id();
            if (!$createdBy) {
                Log::error('Skipping Row - No Authenticated User Found');
                continue;
            }

            Employee::updateOrCreate(
                ['employee_code' => $row['employee_code']], 
                [
                    'first_name' => $row['first_name'] ?? null,
                    'last_name' => $row['last_name'] ?? null,
                    'email' => $row['email'] ?? null,
                    'country_code' => '+20',
                    'phone_number' => $row['phone_number'] ?? null,
                    'gender' => $row['gender'] ?? null,
                    'birth_date' => $row['birth_date'] ?? null,
                    'national_id' => $row['national_id'] ?? null,
                    'passport_number' => $row['passport_number'] ?? null,
                    'marital_status' => $row['marital_status'] ?? null,
                    'blood_group' => $row['blood_group'] ?? null,
                    'emergency_contact_name' => $row['emergency_contact_name'] ?? null,
                    'emergency_contact_relationship' => $row['emergency_contact_relationship'] ?? null,
                    'emergency_contact_phone' => $row['emergency_contact_phone'] ?? null,
                    'address_en' => $row['address_en'] ?? null,
                    'address_ar' => $row['address_ar'] ?? null,
                    'nationality_id' => $row['nationality_id'] ?? null,
                    'department_id' => $row['department_id'] ?? null,
                    'position_id' => $row['position_id'] ?? null,
                    'supervisor_id' => $row['supervisor_id'] ?? null,
                    'branch_id' => $row['branch_id'] ?? null,
                    'hire_date' => $row['hire_date'] ?? null,
                    'salary' => $row['salary'] ?? null,
                    'daily_excuse_hours' => $row['daily_excuse_hours'] ?? 0,
                    'monthly_excuse_hours' => $row['monthly_excuse_hours'] ?? 0,
                    'assurance_salary' => $row['assurance_salary'] ?? null,
                    'assurance_number' => $row['assurance_number'] ?? null,
                    'bank_account' => $row['bank_account'] ?? null,
                    'employment_type' => $row['employment_type'] ?? null,
                    'status' => $row['status'] ?? 'active',
                    'notes' => $row['notes'] ?? null,
                    'is_biometric' => $row['is_biometric'] ?? 0,
                    'biometric_id' => $row['biometric_id'] ?? null,
                    'created_by' => $createdBy,
                ]
            );
        }
    }
}
