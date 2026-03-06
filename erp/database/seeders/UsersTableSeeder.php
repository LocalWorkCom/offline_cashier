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

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {

        DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable foreign key checks if necessary

        // Truncate the table to avoid duplication
        DB::table('users')->truncate();
        $country_id = Country::where('phone_code', '+20')->value('id');

        // Users data
        $users = [
            [
                'name' => 'MasterCodeAdmin',
                'email' => 'mastercodeadmin@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'national_id' => Str::random(14),
                'country_id' => $country_id,
                'country_code' => '+20',
                'phone' => '01021356984',
                'code' => 'MCA001',
                'rule_id' => null,
                'flag' => 'admin',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'google_id' => null,
                'facebook_id' => null,
            ],
            [
                'name' => 'SuperAdmin',
                'email' => 'superadmin@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'national_id' => Str::random(14),
                'country_id' => $country_id,
                'country_code' => '+20',
                'phone' => '01123504587',
                'code' => 'SA001',
                'rule_id' => 'superadmin',
                'flag' => 'admin',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'google_id' => null,
                'facebook_id' => null,
            ],
            [
                'name' => 'BranchManager',
                'email' => 'branchmanager@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'national_id' => Str::random(14),
                'country_id' =>$country_id,
                'country_code' => '+20',
                'phone' => '01052458745',
                'code' => 'BM001',
                'rule_id' => null,
                'flag' => 'admin',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'google_id' => null,
                'facebook_id' => null,
            ],
            [
                'name' => 'Officer',
                'email' => 'officer@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'national_id' => Str::random(14),
                'country_id' => $country_id,
                'country_code' => '+20',
                'phone' => '01202365487',
                'code' => 'O001',
                'rule_id' => 'officer',
                'flag' => 'employee',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'google_id' => null,
                'facebook_id' => null,
            ],
            [
                'name' => 'Kitchen Manager',
                'email' => 'KitchenManager@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'national_id' => Str::random(14),
                'country_id' =>$country_id,
                'country_code' => '+20',
                'phone' => '01052458747',
                'code' => 'KM001',
                'rule_id' => null,
                'flag' => 'admin',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'google_id' => null,
                'facebook_id' => null,
            ],
            [
                'name' => 'Head Board',
                'email' => 'HeadBoard@example.com',
                'email_verified_at' => Carbon::now(),
                'password' => Hash::make('password123'),
                'national_id' => Str::random(14),
                'country_id' =>$country_id,
                'country_code' => '+20',
                'phone' => '01052458777',
                'code' => 'HB001',

                'rule_id' => null,
                'flag' => 'admin',
                'is_active' => 1,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'google_id' => null,
                'facebook_id' => null,
            ], [
                'name' => 'Unknown',
                'email' => 'unknown@example.com',
                'email_verified_at' => null,
                'password' => Hash::make('password123'),
                'national_id' => Str::random(14),
                'country_id' => null,
                'country_code' => null,
                'phone' => null,
                'code' => 'U001',
                'rule_id' => null,
                'flag' => 'unknown',
                'is_active' => 0,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'google_id' => null,
                'facebook_id' => null,
            ]
        ];

        // Insert the users data
        $userRecords = DB::table('users')->insert($users);

        // Create employee records for Officer, BranchManager, Kitchen Manager, and HeadBoard
        $usersToCreateEmployeesFor = ['Officer', 'BranchManager', 'Kitchen Manager', 'Head Board'];

        foreach ($usersToCreateEmployeesFor as $role) {
            $user = User::where('name', $role)->first();

            if ($user) {
                Employee::create([
                    'user_id' => $user->id,
                    'employee_code' => $user->code,
                    'first_name' => $user->name,
                    'last_name' => 'Admin',
                    'email' => $user->email,
                    'country_code' => $user->country_code,
                    'phone_number' => $user->phone,
                    'gender' => 'Male',
                    'status' => 'active',
                    'flag' => $role,
                    'created_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
    }


