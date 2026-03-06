<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class APICodeTableSeeder extends Seeder
{
    public function run()
    {
        $sizes = [
            // Clothes Sizes (category_id = 1)
            ['code' => '1', 'api_code_title_en' => 'Success', 'api_code_title_ar' => 'صحيح', 'api_code_message_en' => 'Success Message', 'api_code_message_ar' => 'طلب صحيح'],
            ['code' => '2', 'api_code_title_en' => 'Failed', 'api_code_title_ar' => 'غير صحيح', 'api_code_message_en' => 'Failed Message', 'api_code_message_ar' => 'طلب غير صحيح'],
            ['code' => '3', 'api_code_title_en' => 'Attention', 'api_code_title_ar' => 'انتبه', 'api_code_message_en' => 'The new password cannot be the same as the current password', 'api_code_message_ar' => 'لا يمكن أن تكون كلمة المرور الجديدة هي نفس كلمة المرور الحالية'],
            ['code' => '4', 'api_code_title_en' => 'Success', 'api_code_title_ar' => 'تم بنجاح', 'api_code_message_en' =>  "User logged out", 'api_code_message_ar' => 'يجب تسجيل الدخول أولا'],
            ['code' => '5', 'api_code_title_en' => "Failed", 'api_code_title_ar' =>  'غير صحيح', 'api_code_message_en' =>  "Please , Login to access this route", 'api_code_message_ar' => 'التوكين غير صحيح'],
            ['code' => '6', 'api_code_title_en' => "Category can't delete", 'api_code_title_ar' => 'التصنيف لا يمكن حذفه', 'api_code_message_en' =>  "Category cannot be deleted as it has associated product", 'api_code_message_ar' => 'التصنيف لا يمكن حذفه لانه مرتبط بالمنتج'],
            ['code' => '7', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'حدث خطأ', 'api_code_message_en' =>  "You cannot update the opening balance because there are transactions on this product in this store after the creation date.", 'api_code_message_ar' => "لا يمكنك تعديل الرصيد الافتتاحي لأن هناك معاملات على المنتج في هذا المتجر بعد هذا التاريخ."],
            ['code' => '8', 'api_code_title_en' => "NotExist", 'api_code_title_ar' => 'لا يوجد', 'api_code_message_en' =>  "Data is not exist.", 'api_code_message_ar' => "البيانات غير موجودة"],
            ['code' => '9', 'api_code_title_en' => "AlreadyExist", 'api_code_title_ar' => 'موجود بالفعل ', 'api_code_message_en' =>  "Data is already exist.", 'api_code_message_ar' => "البيانات موجودة بالفعل"],
            ['code' => '10', 'api_code_title_en' => "NoChange", 'api_code_title_ar' => 'لا يوجد تغير', 'api_code_message_en' =>  "Data is not changed.", 'api_code_message_ar' => "لا يوجد تحديث"],
            ['code' => '11', 'api_code_title_en' => "CouponNotValid", 'api_code_title_ar' => 'قسيمة خصم غير صالحة', 'api_code_message_en' =>  "Coupon is not valid", 'api_code_message_ar' => "القسمية غير صالحة للاستخدام"],
            ['code' => '12', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "User logged in successfully.", 'api_code_message_ar' => "تم تسجيل الدخول بنجاح"],
            ['code' => '13', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'غير صحيح', 'api_code_message_en' =>  "ُEmail or password is incorrect.", 'api_code_message_ar' => "البريد الإلكتروني أو كلمة المرور غير صحيحة"],
            ['code' => '14', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'غير صحيح', 'api_code_message_en' =>  "ُFailed to generate token.", 'api_code_message_ar' => "حدث خطأ يرجى المحاولة مرة أخرى"],
            ['code' => '15', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "ُPassword changed successfully.", 'api_code_message_ar' => "تم تغيير كلمة المرور بنجاح"],
            ['code' => '16', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "ُUser logged out successfully.", 'api_code_message_ar' => "تم تسجيل الخروج بنجاح"],
            ['code' => '17', 'api_code_title_en' => "NotFound", 'api_code_title_ar' => 'لا يوجد بيانات', 'api_code_message_en' =>  "ُClient data not found.", 'api_code_message_ar' => "لم يتم العثور على بيانات العميل"],
            ['code' => '18', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "ُClient data retrieved successfully.", 'api_code_message_ar' => "تم عرض تفاصيل العميل بنجاح"],
            ['code' => '19', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "ُProfile updated successfully.", 'api_code_message_ar' => "تم تحديث البيانات بنجاح"],
            ['code' => '20', 'api_code_title_en' => "NotFound", 'api_code_title_ar' => 'لا يوجد', 'api_code_message_en' =>  "ُNo ordrers found.", 'api_code_message_ar' => "لا توجد طلبات"],
            ['code' => '21', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "ُOrder is reorded successfully.", 'api_code_message_ar' => "تم اعادة الطلب بنجاح"],
            ['code' => '22', 'api_code_title_en' => "NotFound", 'api_code_title_ar' => 'لا يوجد', 'api_code_message_en' =>  "ُOrder not found.", 'api_code_message_ar' => "الطلب غير موجود"],
            ['code' => '23', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "User created successfully.", 'api_code_message_ar' => "تم التسجيل بنجاح"],
            ['code' => '24', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'حدث خطأ', 'api_code_message_en' =>  'More than one point calculation system cannot be activated', 'api_code_message_ar' => 'لا يمكن تفعيل اكثر من نظام لحساب النقاط'],
            ['code' => '25', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'حدث خطأ', 'api_code_message_en' => 'All systems are disabled and the system must be activated', 'api_code_message_ar' => 'كل الانظمه غير مفعله و يحب تفعيل نظام'],
            ['code' => '26', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "Purchase Invoices retrieved successfully.", 'api_code_message_ar' => "تم عرض تفاصيل فواتير المشتريات بنجاح"],
            ['code' => '27', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "Purchase Invoice created successfully.", 'api_code_message_ar' => "تم تخزين فاتورة المشتريات بنجاح"],
            ['code' => '28', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'حدث خطأ', 'api_code_message_en' => 'Cannot update purchase invoice linked to a store transaction', 'api_code_message_ar' => 'لا يمكن التعديل على فاتورة مشتريات مربوطة بحركة في المخزن'],
            ['code' => '29', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "Purchase invoice (refund) updated successfully.", 'api_code_message_ar' => "تم تعديل فاتورة المشتريات (مسترجع) بنجاح"],
            ['code' => '30', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "Reports fetched successfully.", 'api_code_message_ar' => "تم عرض التقارير بنجاح"],
            ['code' => '31', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'حدث خطأ', 'api_code_message_en' =>  "Expiration date must be after the creation date.", 'api_code_message_ar' => "تاريخ الانتهاء يجب ان يكون بعد تاريخ الانشاء"],
            ['code' => '32', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "Gift applied successfully to specified users.", 'api_code_message_ar' => "تم تعيين الهدية للمستخدمين المحددين"],
            ['code' => '33', 'api_code_title_en' => "Success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "Gift applied successfully to users in specified branch.", 'api_code_message_ar' => "تم تعيين الهدية للمستخدمين التابعين للفرع المحدد"],
            ['code' => '34', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'عفوا', 'api_code_message_en' =>  "Order Can't cancel after now.", 'api_code_message_ar' => "لا يمكن الغاء الطلب بعد الان"],
            ['code' => '35', 'api_code_title_en' => "success", 'api_code_title_ar' => 'صحيح', 'api_code_message_en' =>  "Phone number verified successfully.", 'api_code_message_ar' => "تم التأكد من رقم الهاتف بنجاح"],

            ['code' => '102', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'عفوا', 'api_code_message_en' =>  "forbidden", 'api_code_message_ar' => "غير مسموح"],
            ['code' => '101', 'api_code_title_en' => "Failed", 'api_code_title_ar' => 'حدث خطأ', 'api_code_message_en' =>  "this table is already busy.", 'api_code_message_ar' => "هذه الطاوله مشغوله الان"],

        ];

        DB::table('apicodes')->insert($sizes);
    }
}
