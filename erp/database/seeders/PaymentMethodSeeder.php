<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PaymentMethod;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $paymentMethods = [
            [
                'type' => 'cash',
                'name_en' => 'Cash',
                'name_ar' => 'نقدي',
                'description_en' => 'Pay with cash on delivery or at location',
                'description_ar' => 'الدفع نقداً عند الاستلام أو في الموقع',
                'additional_info' => 'Available for all vendors',
                'status' => 1,
                'all_vendors' => 1,
                'vendor_ids' => null,
            ],
            [
                'type' => 'visa',
                'name_en' => 'Card',
                'name_ar' => 'بطاقة',
                'description_en' => 'Pay using credit or debit cards',
                'description_ar' => 'الدفع باستخدام البطاقات الائتمانية أو البنكية',
                'additional_info' => 'Supports all major card networks',
                'status' => 1,
                'all_vendors' => 1,
                'vendor_ids' => null,
            ],
            [
                'type' => 'visa',
                'name_en' => 'Cheque',
                'name_ar' => 'شيك',
                'description_en' => 'Pay with personal or bank cheque',
                'description_ar' => 'الدفع بشيك شخصي أو بنكي',
                'additional_info' => 'Requires clearance period',
                'status' => 1,
                'all_vendors' => 1,
                'vendor_ids' => null,
            ],
            [
                'type' => 'visa',
                'name_en' => 'Bank Transfer',
                'name_ar' => 'تحويل بنكي',
                'description_en' => 'Direct bank transfer payment',
                'description_ar' => 'الدفع عن طريق التحويل البنكي المباشر',
                'additional_info' => 'Bank details will be provided',
                'status' => 1,
                'all_vendors' => 1,
                'vendor_ids' => null,
            ],
            [
                'type' => 'visa',
                'name_en' => 'Credit/Debit Card',
                'name_ar' => 'بطاقة ائتمان / مدين',
                'description_en' => 'Secure online card payment',
                'description_ar' => 'دفع آمن عبر الإنترنت بالبطاقة',
                'additional_info' => '3D Secure enabled',
                'status' => 1,
                'all_vendors' => 1,
                'vendor_ids' => null,
            ],
            [
                'type' => 'visa',
                'name_en' => 'Mobile Wallet',
                'name_ar' => 'محفظة رقمية',
                'description_en' => 'Pay using mobile wallet applications',
                'description_ar' => 'الدفع باستخدام تطبيقات المحفظة الرقمية',
                'additional_info' => 'Supports Apple Pay and Google Pay',
                'status' => 1,
                'all_vendors' => 1,
                'vendor_ids' => null,
            ],
            [
                'type' => 'visa',
                'name_en' => 'Online Gateway',
                'name_ar' => 'بوابة الدفع الإلكتروني',
                'description_en' => 'Secure online payment through payment gateways',
                'description_ar' => 'دفع إلكتروني آمن عبر بوابات الدفع',
                'additional_info' => 'Multiple gateway options available',
                'status' => 1,
                'all_vendors' => 1,
                'vendor_ids' => null,
            ],
        ];

        foreach ($paymentMethods as $method) {
            PaymentMethod::create($method);
        }

        $this->command->info('Payment methods seeded successfully!');
    }
}
