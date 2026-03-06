<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\StorageLocation;
use App\Models\Store;
use App\Models\Zone;
use App\Services\Inventory_Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuditController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');


        $response = $this->auditService->index();

        // Then apply pagination
        $paginated = paginateOrGetAll($response, $request, null, null);
        $paginated['data'] = collect($paginated['data'])->map(function ($audit) use ($lang) {
            return [
                'id' => $audit->id,
                'audit_number' => $audit->audit_number,
                'status_translated' => $this->auditService->translateStatus($audit->status, $lang),
                'status' => $audit->status,
            ];
        });

        return ResponseWithSuccessDataPaginated($lang, $paginated, 1);
    }


    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $audit = $this->auditService->show($id);
        if (!$audit) {
            $message = $lang === 'ar' ? 'هذا الجرد غير موجود' : 'Audit not found';
            return respondError($message, 404);
        }
        $auditArray = $audit->toArray();
        $auditArray['items'] = collect($auditArray['items'])->map(function ($item) {
            $item['difference_qty'] = $item['system_qty'] - $item['actual_qty'];
            // $barcode = $item['barcode'] ?? null;
            //    $item['barcode'] = $barcode;
            // $item['barcode'] = [
            //     'id'   => $barcode,
            //     'name' => $barcode,
            // ];
            return $item;
        })->toArray();
        return ResponseWithSuccessData(
            $lang,
            $auditArray,
            1
        );
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $validator = Validator::make($request->all(), [
            'audit_type' => 'required|in:periodic,surprise',
            'period' => 'required|in:daily,weekly,monthly,once',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'scope' => 'nullable|in:full,partial,location',
            'scope_type' => 'nullable|in:zone,location,category,product,branch,store',
            'scope_ids' => 'nullable|array',
            // 'status' => 'nullable|in:draft,pending,being_audited,Done',
            'responsible_user_id' => 'nullable|integer|exists:employees,id',
        ]);

        if ($validator->fails()) {
            return respondError(__('validation.error'), 400, $validator->errors());
        }
        $scopeTables = [
            'zone'     => 'zones',
            'location' => 'storage_locations',
            'category' => 'categories',
            'product'  => 'product_brands',
            'branch'   => 'branches',
            'store'    => 'stores',
        ];

        if (!empty($validated['scope_type']) && !empty($validated['scope_ids'])) {

            $table = $scopeTables[$validated['scope_type']];

            // Count valid IDs found in database
            $count = DB::table($table)
                ->whereIn('id', $validated['scope_ids'])
                ->count();

            if ($count !== count($validated['scope_ids'])) {
                $message = $lang == 'en'
                    ? "Some IDs in scope_ids do not exist in table {$table}."
                    : "بعض المعرفات في scope_ids غير موجودة في جدول {$table}.";
                return respondError($message, 400, ['error' => $message]);
            }
        }
        // Check that dates are logically correct depending on period
        $from = \Carbon\Carbon::parse($validated['from_date']);
        $to = \Carbon\Carbon::parse($validated['to_date']);
        $period = $validated['period'];
        if ($validated['audit_type'] == 'surprise') {
            if ($period == 'once') {
                // For one-time audits, from_date and to_date should be the same
                if (!$from->isSameDay($to)) {
                    $message = $lang == 'en' ? 'For one-time audits, from_date and to_date must be the same.' : 'بالنسبة للتدقيقات لمرة واحدة، يجب أن يكون from_date و to_date متطابقين.';
                    return respondError(__('validation.error'), 400, ['error'=>$message]);

                }
            } else {
                $message = $lang == 'en' ? 'Surprise audits must have the period set to "once".' : 'يجب أن يكون نوع الفترة للتدقيقات المفاجئة "مرة واحدة".';
                return respondError(__('validation.error'), 400, ['error'=>$message]);

            }
        }

        if ($period === 'weekly') {

            $totalDays = $from->diffInDays($to) + 1;

            // Calculate number of full weeks
            $fullWeeks = intdiv($totalDays, 7);  // floor division

            if ($fullWeeks < 1) {
                $message = $lang == 'en'
                    ? 'Weekly audits must cover at least one full week (7 days).'
                    : 'يجب أن تغطي التدقيقات الأسبوعية أسبوعًا واحدًا كاملًا على الأقل (7 أيام).';
                return respondError(__('validation.error'), 400, ['error'=>$message]);

            }
        }


        if ($period === 'monthly') {
            $totalDays = $from->diffInDays($to) + 1;
            if ($totalDays % 30 !== 0) {
                $message = $lang == 'en' ? 'The number of days between from_date and to_date must be divisible by 30 for monthly audits.' : 'يجب أن يكون عدد الأيام بين from_date و to_date قابلاً للقسمة على 30 للتدقيقات الشهرية.';
            }
        }
        if ($validated['scope'] == 'location') {
            $validated['scope_type'] =  'location';
        }
            if ($validated['scope'] == 'full') {
            $validated['scope_type'] =  'branch';
        }
        $audit = $this->auditService->store($validated, $lang);

        return ResponseWithSuccessData($lang, $audit, 1);
    }
    public function getAuditProducts(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $audit = Audit::find($id);

        if (!$audit) {
            return respondError('Audit not found', 404);
        }

        $scopeIds = json_decode($audit->scope_ids, true);

        if (empty($scopeIds) || !is_array($scopeIds)) {
            return respondError('Invalid or missing scope IDs', 422);
        }
        $audit = $this->auditService->getAuditProducts($id);

        return ResponseWithSuccessData($lang, $audit, 1);
    }


    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'audit_type' => 'nullable|in:periodic,surprise',
            'period' => 'nullable|in:daily,weekly,monthly',
            'from_date' => 'required|date|after_or_equal:today',
            'to_date' => 'required|date|after_or_equal:from_date',
            'audit_date' => 'nullable|date',
            'scope' => 'nullable|in:full,partial,location',
            'scope_type' => 'nullable|in:zone,location,category,product,branch,store',
            'scope_ids' => 'nullable|array',
            'status' => 'nullable|in:draft,pending,being_audited,Done',
        ]);

        $audit = $this->auditService->update($id, $validated);
        $audit->refresh();

        $audit->status_translated = $this->auditService->translateStatus($audit->status, $lang);
        return ResponseWithSuccessData(
            $lang,
            $audit,
            1
        );
    }
    public function addItems(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $validated = Validator::make($request->all(), [
            'audit_items' => 'required|array|min:1',
            'audit_items.*.product_brand_id' => 'required|integer|exists:product_brands,id',
            'audit_items.*.barcode' => 'required|string|max:255',
            'audit_items.*.unit_id' => 'nullable|integer|exists:units,id',
            'audit_items.*.actual_qty' => 'nullable|numeric|min:0',
            'audit_items.*.reason_id' => 'nullable|integer|exists:discrepancy_reasons,id',
        ]);

        if ($validated->fails()) {
            return RespondWithBadRequestWithData($validated->errors());
        }

        // Check for discrepancy reason requirement
        foreach ($request->audit_items as $item) { // Changed from $request->items to $request->audit_items
            $systemQty = getProductQuantity($item['product_brand_id'], 'base', $item['barcode']);
            $actualQty = $item['actual_qty'] ?? $systemQty;

            if ($actualQty > $systemQty) {
                $message = $lang === 'en'
                    ? 'Actual quantity cannot be greater than system quantity.'
                    : 'لا يمكن أن تكون الكمية الفعلية أكبر من الكمية النظامية.';

                return respondError($message, 400);
            }
            if ($actualQty != $systemQty && empty($item['reason_id'])) {
                $message = $lang === 'en'
                    ? 'Discrepancy reason is required when actual quantity differs from system quantity.'
                    : 'سبب التفاوت مطلوب عندما تختلف الكمية الفعلية عن الكمية النظامية.';

                return respondError($message, 400);
            }
        }

        $audit = Audit::find($id);
        if (!$audit) {
            $message = $lang === 'en'
                ? 'Audit not found.'
                : 'لم يتم العثور على الجرد.';

            return respondError($message, 404);
        }
        if ($audit->status == 'Done') {
            $message = $lang === 'en'
                ? 'Cannot add items to a completed audit.'
                : 'لا يمكن  إضافة او تعديل عناصر إلى تدقيق مكتمل.';

            return respondError($message, 400);
        }

        $audit = $this->auditService->addItems($id, $request->audit_items); // Changed from $request->items to $request->audit_items

        return ResponseWithSuccessData($lang, $audit, 1);
    }
    public function changeStatus(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        $status = $request->input('status');
        $audit = Audit::find($id);

        if (!$audit) {
            $message = $lang == 'en' ? 'Audit not found.' : 'لم يتم العثور على الجرد.';
            return respondError($message, 400);
        }
        if ($audit->status == 'Done') {
            $message = $lang == 'en'
                ? 'Audit is already completed. Status cannot be changed.'
                : 'تم الانتهاء من التدقيق بالفعل. لا يمكن تغيير الحالة.';
            return respondError($message, 400);
        }
        if ($audit->status == 'draft' && $status != 'pending') {
            $message = $lang == 'en'
                ? 'You can only change status from draft to pending.'
                : 'يمكنك فقط تغيير الحالة من مسودة إلى قيد الانتظار.';
            return respondError($message, 400);
        }
        if ($audit->status == 'pending' && $status != 'being_audited') {
            $message = $lang == 'en'
                ? 'You can only change status from pending to being_audit.'
                : 'يمكنك فقط تغيير الحالة من قيد الانتظار إلى قيد المراجعة.';
            return respondError($message, 400);
        }
        if ($audit->status == 'being_audited' && $status != 'Done') {
            $message = $lang == 'en'
                ? 'You can only change status from being_audit to Done.'
                : 'يمكنك فقط تغيير الحالة من قيد المراجعة إلى تم.';
            return respondError($message, 400);
        }
        if ($status == 'Done') {
            $this->auditService->UpdateStock($audit);
        }
        $audit = $this->auditService->ChangeStatus($id, $status);

        $audit->refresh();

        $audit->status_translated = $this->auditService->translateStatus($audit->status, $lang);
        return ResponseWithSuccessData($lang, $audit, 1);
    }


    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $this->auditService->destroy($id);
        return RespondWithSuccessMsg(
            $lang == 'en' ?  'Deleted Successfully' : 'تم الحذف بنجاح',
        );
    }
    public function getAuditReport(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $audit = Audit::with([
            'items.productBrand.product',
            'items.productBrand.brand',
            'items.reason',
        ])->find($id);
        if (!$audit) {
            $message = $lang == 'en' ? 'Audit not found.' : 'لم يتم العثور على الجرد.';
            return respondError($message, 400);
        }
        $scopeType = $audit->scope_type;
        $scopeIds = is_array($audit->scope_ids)
            ? $audit->scope_ids
            : json_decode($audit->scope_ids, true);

        $scopeName = collect([]);

        switch ($scopeType) {
            case 'store':
                $scopeName = Store::whereIn('id', $scopeIds)->get()->pluck('name');
                break;

            case 'branch':
                $scopeName = Branch::whereIn('id', $scopeIds)->get()->pluck('name');
                break;

            case 'zone':
                $scopeName = Zone::whereIn('id', $scopeIds)->get()->pluck('name');
                break;

            case 'location':
                $scopeName = StorageLocation::whereIn('id', $scopeIds)->get()->pluck('name');
                break;

            case 'category':
                $scopeName = Category::whereIn('id', $scopeIds)->get()->pluck('name');
                break;

            case 'product':
                $scopeName = Product::whereIn('id', $scopeIds)->get()->pluck('name');
                break;
        }

        $data = [
            'audit_number' => $audit->audit_number,
            'audit_type' => $audit->audit_type,
            'period' => $audit->period,
            'from_date' => $audit->from_date,
            'to_date' => $audit->to_date,
            'audit_date' => $audit->audit_date,
            'status' => $audit->status,
            'scope_type' => $audit->scope_type,
            'scope_name' => $scopeName->values(),
            'AllProductsCount' => $audit->items->count(),
            'created_by' => $audit->creator?->name,
            'responsible_user' => $audit->employee?->name,
            'DismatchedProductsCount' => $audit->items->where('actual_qty', '!=', 'system_qty')->count(),
            'PercentageOfDiscrepancy' => $audit->items->count() > 0 ? round(($audit->items->where('actual_qty', '!=', 'system_qty')->count() / $audit->items->count()) * 100, 2) : 0,
            'items' => $audit->items->map(function ($item) {
                $difference = ($item->actual_qty ?? 0) - ($item->system_qty ?? 0);
                return [
                    'product_name' => $item->productBrand?->product?->name ?? '',
                    'brand_name' => $item->productBrand?->brand?->name ?? '',
                    'system_qty' => $item->system_qty,
                    'actual_qty' => $item->actual_qty,
                    'difference' => $difference,
                    'discrepancy_reason' => $item->reason?->name ?? null,
                ];
            }),
        ];

        return ResponseWithSuccessData($lang, $data, 1);
    }
}
