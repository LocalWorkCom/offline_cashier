<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['name_ar' => 'طلب', 'name_en' => 'order', 'active' => true],
            ['name_ar' => 'فاتورة', 'name_en' => 'invoice', 'active' => true],
            ['name_ar' => 'طاولة', 'name_en' => 'table', 'active' => true],
            ['name_ar' => 'طلب', 'name_en' => 'order', 'active' => true],
            ['name_ar' => 'إجازة وحضور', 'name_en' => 'Leave & Attendance', 'active' => true],
            ['name_ar' => 'رواتب وتعويضات', 'name_en' => 'Payroll & Compensation', 'active' => true],
            ['name_ar' => 'أداء وتقييم', 'name_en' => 'Performance & Evaluation', 'active' => true],
            ['name_ar' => 'تحديث سياسة', 'name_en' => 'Policy update', 'active' => true],
            ['name_ar' => 'إعلانات الإدارة', 'name_en' => 'Management Announcements', 'active' => true],
            ['name_ar' => 'أخبار الموارد البشرية', 'name_en' => 'HR News', 'active' => true],
            ['name_ar' => 'عام', 'name_en' => 'General', 'active' => true],
        ];

        DB::table('notification_categories')->insert($categories);

        // Map old notifications.notify_type string values to new category IDs
        
        $map = [
            'order' => DB::table('notification_categories')->where('name_en', 'order')->value('id'),
            'invoice' => DB::table('notification_categories')->where('name_en', 'invoice')->value('id'),
            'table' => DB::table('notification_categories')->where('name_en', 'table')->value('id'),
        ];

        foreach ($map as $oldValue => $newId) {
            DB::table('notifications')
                ->where('notify_type', $oldValue)
                ->update(['notify_type' => $newId]);
        }
    }
}
