<?php

namespace Database\Seeders;

use Illuminate\Support\Carbon;
use Illuminate\Database\Seeder;
use App\Models\DirectSupplyPermissionStatusSetting;

class DirectSupplyPermissionStatusSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = [
            [
                'name_ar' => 'مسودة',
                'name_en' => 'Draft',
                'description_ar' => 'تم إنشاء إذن التوريد المباشر ولكن لم يتم تقديمه بعد.',
                'description_en' => 'DSP created but not yet submitted.',
                'type' => 'start',
                'previous_statuses' => null, // No previous status for start
                'next_statuses' => [2], // Can transition to Submitted
                'behavior' => 'manual',
                'active' => true,
                'position' => 1,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1, // Assuming user ID 1 exists
            ],
            [
                'name_ar' => 'مقدم',
                'name_en' => 'Submitted',
                'description_ar' => 'تم تقديم إذن التوريد المباشر ولكن الشحنة لم يتم استلامها بعد.',
                'description_en' => 'DSP submitted but shipment not yet received.',
                'type' => 'intermediate',
                'previous_statuses' => [1], // Can transition from Draft
                'next_statuses' => [3, 4, 5], // Can transition to Received, Not Received, Cancelled
                'behavior' => 'manual',
                'active' => true,
                'position' => 2,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ],
            [
                'name_ar' => 'مستلم',
                'name_en' => 'Received',
                'description_ar' => 'تم استلام الشحنة فعلياً ولكن لم يتم اختبارها أو إضافتها بعد.',
                'description_en' => 'Shipment physically received but not yet tested or added.',
                'type' => 'intermediate',
                'previous_statuses' => [2, 6], // Can transition from Submitted, Partially Received
                'next_statuses' => [7, 10, 9], // Can transition to Being Tested, Received and Added, Issued
                'behavior' => 'manual',
                'active' => true,
                'position' => 3,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ],
            [
                'name_ar' => 'غير مستلم',
                'name_en' => 'Not Received',
                'description_ar' => 'تم تقديم إذن التوريد المباشر ولكن الشحنة لم يتم استلامها بعد.',
                'description_en' => 'DSP submitted but shipment not yet received.',
                'type' => 'intermediate',
                'previous_statuses' => [2], // Can transition from Submitted
                'next_statuses' => [5, 3], // Can transition to Cancelled, Received
                'behavior' => 'manual',
                'active' => true,
                'position' => 4,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ],
            [
                'name_ar' => 'ملغى',
                'name_en' => 'Cancelled',
                'description_ar' => 'تم إلغاء إذن التوريد المباشر قبل الاستلام أو بسبب خطأ المستخدم.',
                'description_en' => 'DSP cancelled before receipt or due to user mistake.',
                'type' => 'final',
                'previous_statuses' => [1, 2, 4], // Can transition from Draft, Submitted, Not Received
                'next_statuses' => null, // No next status for final
                'behavior' => 'manual',
                'active' => true,
                'position' => 5,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ],
            [
                'name_ar' => 'مستلم جزئياً',
                'name_en' => 'Partially Received',
                'description_ar' => 'تم استلام بعض الكميات؛ والبعض الآخر لا يزال قيد الانتظار.',
                'description_en' => 'Some quantities received; others still pending.',
                'type' => 'intermediate',
                'previous_statuses' => [2], // Can transition from Submitted
                'next_statuses' => [3, 7], // Can transition to Received, Being Tested
                'behavior' => 'manual',
                'active' => true,
                'position' => 6,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ],
            [
                'name_ar' => 'قيد الاختبار',
                'name_en' => 'Being Tested',
                'description_ar' => 'المواد المستلمة تحت الاختبار والفحص الجودة.',
                'description_en' => 'Received items are under quality testing.',
                'type' => 'intermediate',
                'previous_statuses' => [3, 6], // Can transition from Received, Partially Received
                'next_statuses' => [9, 10, 8], // Can transition to Issued, Received and Added, Returned
                'behavior' => 'manual',
                'active' => true,
                'position' => 7,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ],
            [
                'name_ar' => 'معاد',
                'name_en' => 'Returned',
                'description_ar' => 'تم إعادة المواد رسمياً بسبب مشكلة محددة.',
                'description_en' => 'Items officially returned due to identified issue.',
                'type' => 'final',
                'previous_statuses' => [9], // Can transition from Issued
                'next_statuses' => null, // No next status for final
                'behavior' => 'manual',
                'active' => true,
                'position' => 8,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ]
            ,
            // [
            //     'name_ar' => 'صادر',
            //     'name_en' => 'Issued',
            //     'description_ar' => 'تم تحديد مشكلة أثناء الاختبار أو التفتيش.',
            //     'description_en' => 'Issue identified during testing or inspection.',
            //     'type' => 'intermediate',
            //     'previous_statuses' => [7], // Can transition from Being Tested
            //     'next_statuses' => [8], // Can transition to Returned
            //     'behavior' => 'manual',
            //     'active' => true,
            //     'position' => 9,
            //     'created_at' => Carbon::now(),
            //     'modified_at' => Carbon::now(),
            //     'created_by' => 1,
            // ],
            [
                'name_ar' => 'مستلم ومضاف',
                'name_en' => 'Received and Added',
                'description_ar' => 'تم استلام الشحنة وإضافتها بنجاح إلى مخزون المستودع.',
                'description_en' => 'Shipment received and successfully added to warehouse stock.',
                'type' => 'final',
                'previous_statuses' => [3, 7, 6], // Can transition from Received, Being Tested, Partially Received
                'next_statuses' => null, // No next status for final
                'behavior' => 'automatic', // Can be automatic or manual
                'active' => true,
                'position' => 10,
                'created_at' => Carbon::now(),
                'modified_at' => Carbon::now(),
                'created_by' => 1,
            ],
        ];

        foreach ($statuses as $status) {
            DirectSupplyPermissionStatusSetting::create($status);
        }
    }
}