<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB ;

class OrderCancellationReasonsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $reasons = [
            ['reason_ar' => 'العميل غير متوفر عند التوصيل', 'reason_en' => 'Customer not available at delivery'],
            ['reason_ar' => 'العميل رفض استلام الطلب', 'reason_en' => 'Customer refused to receive the order'],
            ['reason_ar' => 'عنوان غير صحيح أو غير واضح', 'reason_en' => 'Incorrect or unclear address'],
            ['reason_ar' => 'تم الطلب عن طريق الخطأ', 'reason_en' => 'Order placed by mistake'],
            ['reason_ar' => 'تأخر في التوصيل', 'reason_en' => 'Delivery delay'],
            ['reason_ar' => 'السعر غير مقبول للعميل', 'reason_en' => 'Price not acceptable to customer'],
            ['reason_ar' => 'طريقة الدفع المفضلة غير متوفرة', 'reason_en' => 'Preferred payment method not available'],
            ['reason_ar' => 'غيّر العميل رأيه', 'reason_en' => 'Customer changed their mind'],
            ['reason_ar' => 'طلب مكرر', 'reason_en' => 'Duplicate order'],
            ['reason_ar' => 'العميل غير قادر على الدفع', 'reason_en' => 'Customer was unable to pay'],
            ['reason_ar' => 'خطأ في تجهيز الطلب', 'reason_en' => 'Error in order preparation'],
            ['reason_ar' => 'غير قادرين على تجهيز الطلب في الوقت المحدد', 'reason_en' => 'Unable to prepare the order on time'],
            ['reason_ar' => 'خطأ في سعر أو تفاصيل المنتج', 'reason_en' => 'Error in product price or details'],
            ['reason_ar' => 'تم الإلغاء بطلب من الإدارة', 'reason_en' => 'Canceled upon management request'],
            ['reason_ar' => 'مشكلة في النظام أو التطبيق', 'reason_en' => 'System or app issue'],
            ['reason_ar' => 'تم تجهيز الطلب بشكل غير صحيح', 'reason_en' => 'Order prepared incorrectly'],
            ['reason_ar' => 'لا يوجد عامل توصيل متاح', 'reason_en' => 'No delivery agent available'],
            ['reason_ar' => 'تأخير في التوصيل من قبل شركة الشحن', 'reason_en' => 'Delivery delay from courier'],
            ['reason_ar' => 'مشكلة في وسائل النقل', 'reason_en' => 'Transportation issue'],
            ['reason_ar' => 'أحوال جوية سيئة منعت التوصيل', 'reason_en' => 'Bad weather prevented delivery'],
            ['reason_ar' => 'عطل في تطبيق التوصيل', 'reason_en' => 'Delivery app malfunction'],
        ];

        $types = ["waiter", "driver", "client", "branch manager"];

        $reasons = array_map(function ($reason) use ($types) {
            $reason['type'] = json_encode($types); // Store as JSON
            return $reason;
        }, $reasons);

        DB::table('order_cancellation_reasons')->insert($reasons);

    }
}
