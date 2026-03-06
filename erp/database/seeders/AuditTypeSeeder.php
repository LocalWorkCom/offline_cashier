<?php

namespace Database\Seeders;

use App\Models\AuditType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AuditTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $audit_types = [
            ['name_en' => 'Periodic', 'name_ar' => 'دوري'],
            ['name_en' => 'Surprise', 'name_ar' => 'مفاجئ'],
        ];

        foreach ($audit_types as $audit_type) {
            AuditType::create($audit_type);
        }
    }
}
