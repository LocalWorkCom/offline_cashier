<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\PermissionRegistrar;

class ProcurementPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        //keys permissions 
        $permissions = [
            "view purchase_employees",
            "create purchase_employees",
            "update purchase_employees",
            "delete purchase_employees",
            "view payment-interval",
            "create payment-interval",
            "update payment-interval",
            "delete payment-interval",
            "view payment-method",
            "create payment-method",
            "update payment-method",
            "delete payment-method",
            "view payment-type",
            "create payment-type",
            "update payment-type",
            "delete payment-type",
        ];

        $insertedIds = [];

        foreach ($permissions as $perm) {
            // Try to create English and Arabic names simply
            $enName = $perm;
            $arName = $this->autoTranslate($perm);

            $id = DB::table('permissions')->insertGetId([
                'name' => $enName,
                'name_en' => $enName,
                'name_ar' => $arName,
                'guard_name' => 'employee',
                'is_global' => 0,
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $insertedIds[] = $id;
        }

        $permissionsexists = [
            "create custom-field",
            "view custom-field",
            "update custom-field",
            "delete custom-field",
            "view category",
            "update category",
            "create category",
            "delete category",
            "view brand",
            "create brand",
            "update brand",
            "delete brand",
            "view departments",
            "create departments",
            "update departments",
            "delete departments",
            "view products",
            "create products",
            "update products",
            "delete products",
            "view unit",
            "create unit",
            "update unit",
            "delete unit",
        ];
        //handle check permission aleady created just get ids to assign
        foreach ($permissionsexists as $permName) {
            // Look for existing permission
            $permission = Permission::where('name', $permName)
                ->where('guard_name', 'employee')
                ->first();

            if (!$permission) {
                // If not found → create it
                $permission = Permission::create([
                    'name'       => $permName,
                    'name_en'    => $permName,
                    'name_ar'    => $this->autoTranslate($permName),
                    'guard_name' => 'employee',
                    'is_global'  => 0,
                    'is_active'  => 1,
                ]);
            }

            $insertedIds[] = $permission->id;
        }
        // Assign all permissions to module_id = 4
        foreach ($insertedIds as $permissionId) {
            DB::table('module_permission')->insert([
                'module_id' => 4,
                'permission_id' => $permissionId,
            ]);
        }
        // Assign all permissions to roles `Purchase_Manager` and `superAdmin`
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // get role and permissions
        $purchaseManager = Role::where('name', 'Purchase_Manager')->where('guard_name', 'employee')->first();
        $superAdmin = Role::where('name', 'superAdmin')->where('guard_name', 'employee')->first();

        $permissionsModels = Permission::whereIn('id', $insertedIds)->get();

        // Replace old permissions for purchase manager
        if ($purchaseManager) {
            $purchaseManager->syncPermissions($permissionsModels); // old removed, new assigned
        }

        // Add permissions to superAdmin without removing old ones
        if ($superAdmin) {
            $superAdmin->givePermissionTo($permissionsModels);
        }
    }

    /**
     * Quick Arabic translation helper (optional)
     */
    private function autoTranslate(string $text): string
    {
        $map = [
            'view' => 'عرض',
            'create' => 'إضافة',
            'update' => 'تحديث',
            'delete' => 'حذف',
            'approve' => 'موافقة',
            'reject' => 'رفض',
            'changeStatus' => 'تغيير الحالة',
            'approveOrReject' => 'موافقه او الرفض',
            'restore' => 'استعادة',
            'submit' => 'إرسال',
            'assign' => 'تعيين',
        ];

        $objectMap = [
            'audits' => 'التدقيقات',
            'return dsp' => 'إذن الإرجاع',
            'purchase requests' => 'طلبات الشراء',
            'waste report' => 'تقرير الهدر',
            'activity logs' => 'سجل النشاط',
            'inventory setting' => 'إعدادات المخزون',
            'inventory location' => 'مكان المخزن',
            'wastreportnumber' => 'رقم محضر الاعدام',
            'DspNumber' => 'رقم اذن الأضافه المباشر',
            'SupplyOrderNumber' => 'رقم امر التوريد',
            'DirectSupplyPermission' => 'أذن أضافه مباشر',
            'PurchaseRequestNumber' => 'رقم طلب الشراء',
            'supply order reasons' => 'اسباب أمر التوريد',
            'direct supply issue type' => 'مشاكل امر التوريد',
            'DirectSupplyPermissionStatusSetting' => 'حالات أذن أضافه مباشر',
            'reason purchase requests' => 'اسباب طلب الشراء',
            'reject purchase requests' => 'اسباب رفض طلب الشراء',
            'audit type' => 'أنواع الجرد',
            'store_transaction_details' => 'تفاصيل حركه المخزن',
            'store_transactions' => 'حركه المخزن',
            'discrepancy reason' => 'اسباب التباين',
            'purchase requests Suggestion' => 'اقتراحات أمر الشراء',
            'storage location' => 'موقع التخزين',
            'zones' => 'المناطق',
            'store' => 'المخزن',
            'unit' => 'الوحدة',
            'category' => 'الفئة',
            'custom field' => 'الحقل المخصص',
            'waste reason' => 'سبب الهدر',
            'supply orders' => 'أوامر التوريد',
            'profile' => 'الملف الشخصي',
            'statistics' => 'الإحصائيات',
            'dashboard' => 'لوحة التحكم',
            'products' => 'المنتجات',
            'reason_return_dsp' => 'أسباب إرجاع إذن الإضافة المباشر',
            'brand' => 'البراند',
        ];

        $arabic = $text;
        foreach ($map as $en => $ar) {
            if (str_contains($arabic, $en)) {
                $arabic = str_replace($en, $ar, $arabic);
                break;
            }
        }

        $arabic = str_replace(['_', '-'], ' ', $arabic);

        foreach ($objectMap as $en => $ar) {
            if (str_contains($arabic, $en)) {
                $arabic = str_replace($en, $ar, $arabic);
            }
        }

        return trim($arabic);
    }
}
