<?php

namespace Database\Seeders;

use App\Models\Employee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AssignRoleSeeder extends Seeder
{
    public function run()
    {
        // Temporarily disable foreign key checks for development
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('model_has_roles')->truncate();
        DB::table('model_has_permissions')->truncate();
        // If you're recreating all roles, uncomment the line below
        // DB::table('role_has_permissions')->truncate();
        Role::truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $roles = [
            'superAdmin',
            'LocalWork Admin',
            'Branch Manager',
            'officer',
            'Kitchen Manager',
            'Head Board',
            'HR_Manager',
            'HR_Employee',
            'Inventory_Employee',
            'Inventory_Manager',
            'Finance_Manager',
            'Finance_Employee',
        ];

        // Create roles for both guards
        $adminRoles = [];
        $employeeRoles = [];

        foreach ($roles as $roleName) {
            $adminRoles[$roleName] = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'admin']);
            $employeeRoles[$roleName] = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'employee']);
        }

        // Assign roles to User (admin guard)
        $userRoleAssignments = [
            2 => 'superAdmin',
            1 => 'LocalWork Admin',
            3 => 'Branch Manager',
            4 => 'officer',
            5 => 'Kitchen Manager',
            6 => 'Head Board',
            7 => 'HR_Manager',
            8 => 'HR_Employee',
            9 => 'Inventory_Employee',
            10 => 'Inventory_Manager',
            11 => 'Finance_Manager',
            12 => 'Finance_Employee',

        ];

        foreach ($userRoleAssignments as $userId => $roleName) {
            $user = User::find($userId);
            if ($user) {
                $user->assignRole($adminRoles[$roleName]);
            }
        }

        // // Assign roles to Employee (employee guard)
        // foreach ($userRoleAssignments as $empId => $roleName) {
        //     $employee = Employee::find($empId);
        //     if ($employee) {
        //         $employee->assignRole($employeeRoles[$roleName]);
        //     }
        // }

        $flagToRole = [
            'hr'        => 'HR_Employee',
            'finance'   => 'Finance_Employee',
            'inventory' => 'Inventory_Employee',
            'admin'     => 'superAdmin',
            'employee'  => 'officer' // or any general role
        ];

        // Assign roles based on flags
        $employees = Employee::all();

        foreach ($employees as $employee) {
            $roleName = $flagToRole[$employee->flag] ?? null;
            if ($roleName && isset($employeeRoles[$roleName])) {
                $employee->assignRole($employeeRoles[$roleName]);
            }
        }
    }
}
