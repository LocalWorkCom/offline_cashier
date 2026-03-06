<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $positions = [
            ['name_en' => 'officer', 'name_ar' => 'مسئول الرفع', 'description_en' => 'Responsible for overseeing operations and maintaining order.', 'description_ar' => 'مسؤول عن مراقبة العمليات والحفاظ على النظام.'],
            ['name_en' => 'waiter', 'name_ar' => 'نادل', 'description_en' => 'Responsible for serving food and drinks to customers.', 'description_ar' => 'مسؤول عن تقديم الطعام والشراب للعملاء.'],
            ['name_en' => 'chef', 'name_ar' => 'طباخ', 'description_en' => 'Responsible for cooking meals in the kitchen.', 'description_ar' => 'مسؤول عن طهي الطعام في المطبخ.'],
            ['name_en' => 'cashier', 'name_ar' => 'أمين الصندوق', 'description_en' => 'Handles cash and credit card transactions with customers.', 'description_ar' => 'يتعامل مع المعاملات النقدية وبطاقات الائتمان مع العملاء.'],
            ['name_en' => 'call_center', 'name_ar' => 'مركز الاتصال', 'description_en' => 'Responsible for handling customer inquiries and complaints via phone.', 'description_ar' => 'مسؤول عن التعامل مع استفسارات العملاء وشكاويهم عبر الهاتف.'],
            ['name_en' => 'customer_service', 'name_ar' => 'خدمة العملاء', 'description_en' => 'Provides assistance and support to customers.', 'description_ar' => 'يوفر المساعدة والدعم للعملاء.'],
            ['name_en' => 'driver', 'name_ar' => 'سائق', 'description_en' => 'Responsible for transporting goods or passengers to various locations.', 'description_ar' => 'مسؤول عن نقل البضائع أو الركاب إلى أماكن مختلفة.'],
            ['name_en' => 'kitchen manager', 'name_ar' => 'مدير المطبخ', 'description_en' => 'Oversees kitchen operations and manages kitchen staff.', 'description_ar' => 'يشرف على عمليات المطبخ ويدير موظفي المطبخ.'],
            ['name_en' => 'branch manager', 'name_ar' => 'مدير الفرع', 'description_en' => 'Manages the overall operations of a branch.', 'description_ar' => 'يدير العمليات العامة للفرع.'],
            ['name_en' => 'kitchen staff', 'name_ar' => 'طاقم المطبخ', 'description_en' => 'Assists in food preparation and kitchen maintenance.', 'description_ar' => 'يساعد في إعداد الطعام وصيانة المطبخ.'],
            ['name_en' => 'supervisor', 'name_ar' => 'مشرف', 'description_en' => 'Supervises and oversees the work of other employees.', 'description_ar' => 'يشرف ويراقب عمل الموظفين الآخرين.'],
            ['name_en' => 'employee', 'name_ar' => 'موظف', 'description_en' => 'Performs assigned tasks and responsibilities in the workplace.', 'description_ar' => 'يؤدي المهام والمسؤوليات المعينة في مكان العمل.'],
            ['name_en' => 'Head Board', 'name_ar' => 'مجلس الإدارة', 'description_en' => 'Responsible for the governance and strategic direction of the organization.', 'description_ar' => 'مسؤول عن الحوكمة والاتجاه الاستراتيجي للمنظمة.'],
            ['name_en' => 'Head Chef', 'name_ar' => 'رئيس الطهاة', 'description_en' => 'Leads the kitchen team and ensures food quality and service standards are met.', 'description_ar' => 'يقود فريق المطبخ ويضمن جودة الطعام ومعايير الخدمة.'],
        ];

        $currentTimestamp = Carbon::now();

        foreach ($positions as $position) {
            DB::table('positions')->insert([
                'name_en' => $position['name_en'],
                'name_ar' => $position['name_ar'],
                'description_en' => $position['description_en'],
                'description_ar' => $position['description_ar'],
                'created_by' => null, // Add specific user ID if needed
                'modified_by' => null,
                'deleted_by' => null,
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
            ]);
        }
    }
}
