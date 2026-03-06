<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JobTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $job_types = [
            ['name_en' => 'Permanent', 'name_ar' => 'دائم'],
            ['name_en' => 'Temporary', 'name_ar' => 'مؤقت'],
            ['name_en' => 'Probationary', 'name_ar' => 'اختباري'],
            ['name_en' => 'Intern', 'name_ar' => 'متدرب'],
        ];

        $currentTimestamp = Carbon::now();

        foreach ($job_types as $job_type) {
            DB::table('job_types')->insert([
                'name_en' => $job_type['name_en'],
                'name_ar' => $job_type['name_ar'],
                'created_by' => null,
                'modified_by' => null,
                'deleted_by' => null,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ]);
        }
    }
}
