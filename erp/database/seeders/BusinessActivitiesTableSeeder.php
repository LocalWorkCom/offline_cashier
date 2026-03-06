<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessActivitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate the table to avoid duplication
        DB::table('business_activities')->truncate();

        // Insert sample records
        DB::table('business_activities')->insert([
            [
                'name_ar' => 'النشاط الأول',
                'name_en' => 'First Activity',
                'description_ar' => 'وصف النشاط الأول باللغة العربية',
                'description_en' => 'Description of the first activity in English',
                'logo' => 'activity1_logo.png',
                'is_active' => 1,
                'created_by' => 1,
                'modified_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name_ar' => 'النشاط الثاني',
                'name_en' => 'Second Activity',
                'description_ar' => 'وصف النشاط الثاني باللغة العربية',
                'description_en' => 'Description of the second activity in English',
                'logo' => 'activity2_logo.png',
                'is_active' => 1,
                'created_by' => 1, // assuming user with ID 1 created it
                'modified_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'name_ar' => 'النشاط الثالث',
                'name_en' => 'Third Activity',
                'description_ar' => 'وصف النشاط الثالث باللغة العربية',
                'description_en' => 'Description of the third activity in English',
                'logo' => 'activity3_logo.png',
                'is_active' => 1,
                'created_by' => 1, // assuming user with ID 1 created it
                'modified_by' => null,
                'deleted_by' => null,
                'deleted_at' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable foreign key checks
    }
}
