<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\PermissionRegistrar;

class InventoryPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        //keys permissions
        $permissions = [
            "update waste_report",
            "view purchase_requests Suggestion",
            "changeStatus waste_report",
            "view statistics",
            "view waste_report",
            "view dashboard",
            "create waste_report",
            "delete waste_report",
            "update wastreportnumber",
            "view profile",
            "view inventory-location",
            "view inventory-setting",
            "update inventory-setting",
            "view return_dsp",
            "create return_dsp",
            "update return_dsp",
            "delete return_dsp",
            "approveOrReject return_dsp",
            "view audits",
            "create audits",
            "update audits",
            "delete audits",
            "view products",
            "create products",
            "update products",
            "delete products",
            "update PurchaseRequestNumber",
            "update DirectSupplyPermission",
            "update SupplyOrderNumber",
            "update DspNumber",
            "view unit",
            "update unit",
            "create unit",
            "delete unit",
            "view custom-field",
            "update custom-field",
            "create custom-field",
            "delete custom-field",
            "view category",
            "update category",
            "create category",
            "delete category",
            "view custom fields",
            "view waste_reason",
            "delete waste_reason",
            "update waste_reason",
            "create waste_reason",
            "create supply-order-reasons",
            "view supply-order-reasons",
            "update supply-order-reasons",
            "delete supply-order-reasons",
            "delete direct_supply_issue_type",
            "view direct_supply_issue_type",
            "create direct_supply_issue_type",
            "update direct_supply_issue_type",
            "changeStatus DirectSupplyPermission",
            "view DirectSupplyPermission",
            "create DirectSupplyPermission",
            "update profile",
            "view DirectSupplyPermissionStatusSetting",
            "create DirectSupplyPermissionStatusSetting",
            "update DirectSupplyPermissionStatusSetting",
            "delete DirectSupplyPermissionStatusSetting",
            "view zones",
            "create zones",
            "update zones",
            "archieve zones",
            "restore zones",
            "view store",
            "create store",
            "update store",
            "delete store",
            "restore store",
            "reorder DirectSupplyPermissionStatusSetting",
            "create reason_purchase_requests",
            "update reason_purchase_requests",
            "view reason_purchase_requests",
            "delete reason_purchase_requests",
            "create reject_purchase_requests",
            "update reject_purchase_requests",
            "view reject_purchase_requests",
            "delete reject_purchase_requests",
            "create purchase_requests",
            "update purchase_requests",
            "view purchase_requests",
            "approveOrReject purchase_requests",
            "create audit type",
            "update audit type",
            "view audit type",
            "delete audit type",
            "view discrepancy reason",
            "create discrepancy reason",
            "update discrepancy reason",
            "delete discrepancy reason",
            "delete store_categories",
            "delete store_transaction_details",
            "delete store_transactions",
            "delete stores",
            "update store_categories",
            "update store_transaction_details",
            "update store_transactions",
            "update stores",
            "view store_categories",
            "view store_transaction_details",
            "view store_transactions",
            "view stores",
            "create store_categories",
            "create store_transaction_details",
            "create store_transactions",
            "create stores",
            "view inventory_employees",
            "create inventory_employees",
            "update inventory_employees",
            "delete inventory_employees",
            "view storage location",
            "create storage location",
            "update storage location",
            "delete storage location",
            "restore storage location",
            "changeStatus AccessDashboard",
            "view supply_orders",
            "create supply_orders",
            "update supply_orders",
            "delete supply_orders",
            "submit supply_orders",
            "approve supply_orders",
            "reject supply_orders",
            "delete reject_reasons",
            "update reject_reasons",
            "create reject_reasons",
            "view reject_reasons",
            "create GroupOfPermissions",
            "Assign EmpyloyeePermissions",
            "view activity_logs",

            "view brand",
            "create brand",
            "update brand",
            "delete brand",

            "view reason_return_dsp",
            "create reason_return_dsp",
            "update reason_return_dsp",
            "delete reason_return_dsp"
        ];

        $permissionIds = [];

        foreach ($permissions as $perm) {
            $permission = Permission::firstOrCreate(
                [
                    'name'       => $perm,
                    'guard_name' => 'employee',
                ],
                [
                    'name_en'   => $perm,
                    'name_ar'   => $this->autoTranslate($perm),
                    'is_global' => 0,
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            $permissionIds[] = $permission->id;

            // Attach to module (if not attached)
            DB::table('module_permission')->updateOrInsert(
                [
                    'module_id'     => 2,
                    'permission_id' => $permission->id,
                ],
                []
            );
        }

        // Clear permission cache
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissionsModels = Permission::whereIn('id', $permissionIds)->get();

        // Inventory Manager → replace permissions
        if ($inventoryManager = Role::where('name', 'Inventory_Manager')->where('guard_name', 'employee')->first()) {
            $inventoryManager->syncPermissions($permissionsModels);
        }

        // SuperAdmin → add without removing existing
        if ($superAdmin = Role::where('name', 'superAdmin')->where('guard_name', 'employee')->first()) {
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
