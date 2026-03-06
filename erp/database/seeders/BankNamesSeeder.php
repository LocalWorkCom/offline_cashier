<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BankName;
use Carbon\Carbon; // Optional: if you want to use Carbon instead of now()

class BankNamesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banks = [
            ['name_ar' => 'البنك الأهلي السعودي', 'name_en' => 'National Commercial Bank', 'logo' => 'ncb_logo.png'],
            ['name_ar' => 'مصرف الراجحي', 'name_en' => 'Al Rajhi Bank', 'logo' => 'al_rajhi_logo.png'],
            ['name_ar' => 'البنك الأهلي المتحد', 'name_en' => 'Alinma Bank', 'logo' => 'alinma_logo.png'],
            ['name_ar' => 'ساما بنك', 'name_en' => 'Samba Financial Group', 'logo' => 'samba_logo.png'],
            ['name_ar' => 'بنك الرياض', 'name_en' => 'Riyad Bank', 'logo' => 'riyad_logo.png'],
            ['name_ar' => 'إمارات بنك دبي الوطني', 'name_en' => 'Emirates NBD', 'logo' => 'emirates_nbd_logo.png'],
            ['name_ar' => 'البنك التجاري الدولي', 'name_en' => 'Commercial International Bank (CIB)', 'logo' => 'cib_logo.png'],
            ['name_ar' => 'البنك العربي', 'name_en' => 'Arab Bank', 'logo' => 'arab_bank_logo.png'],
            ['name_ar' => 'البنك الإسلامي في البحرين', 'name_en' => 'Bahrain Islamic Bank', 'logo' => 'bisb_logo.png'],
            ['name_ar' => 'البنك الوطني المصري', 'name_en' => 'National Bank of Egypt', 'logo' => 'nbe_logo.png'],
            ['name_ar' => 'بنك أبوظبي التجاري', 'name_en' => 'Abu Dhabi Commercial Bank', 'logo' => 'adcb_logo.png'],
            ['name_ar' => 'البنك العربي الأردني', 'name_en' => 'Jordan Arab Bank', 'logo' => 'arab_bank_jordan_logo.png'],
            ['name_ar' => 'بنك قطر الوطني', 'name_en' => 'Qatar National Bank', 'logo' => 'qnb_logo.png'],
            ['name_ar' => 'بنك دبي الإسلامي', 'name_en' => 'Dubai Islamic Bank', 'logo' => 'dib_logo.png'],
            ['name_ar' => 'كويت فاينانس هاوس', 'name_en' => 'Kuwait Finance House', 'logo' => 'kfh_logo.png'],
            ['name_ar' => 'بنك أبوظبي الأول', 'name_en' => 'First Abu Dhabi Bank', 'logo' => 'fab_logo.png'],
            ['name_ar' => 'بنك البحرين الإسلامي', 'name_en' => 'Al Baraka Banking Group', 'logo' => 'al_baraka_logo.png'],
        ];

        foreach ($banks as $bank) {
            BankName::create([
                'name_ar' => $bank['name_ar'],
                'name_en' => $bank['name_en'],
                'logo'    => $bank['logo'],
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
