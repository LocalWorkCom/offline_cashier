<?php

namespace App\Services\Inventory_Services;

use App\Models\Audit;
use App\Models\AuditItem;
use App\Models\ProductBrand;
use App\Models\ProductTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AuditService
{
    public function index()
    {
        return Audit::orderBy('created_at', 'desc')
            ->orderBy('updated_at', 'desc');
    }


    public function show($id)
    {
        $audit = Audit::with([
            'items.productBrand.product', // load product info inside each item
            'items.reason',                // if AuditItem has discrepancy reason
            'items.unit'                   // if you have unit relation
        ])->find($id);
        if (!$audit) {
            return null;
        }
        return $audit;
    }

    public function store($data, $lang)
    {
        // Convert scope_ids array → JSON string
        if (isset($data['scope_ids']) && is_array($data['scope_ids'])) {
            $data['scope_ids'] = json_encode($data['scope_ids']);
        }

        $data['created_by'] = authActionSave()['by'];

        // Auto-generate audit number (e.g., AUD-20251029-001)
        $latestId = Audit::max('id') + 1;
        $data['audit_number'] =   str_pad($latestId, 5, '0', STR_PAD_LEFT);

        // Handle periodic audit generation
        if (($data['audit_type'] ?? null) === 'periodic') {
            $from = Carbon::parse($data['from_date']);
            $to = Carbon::parse($data['to_date']);
            $period = $data['period'];

            $audits = [];

            if ($period === 'daily') {
                // Create one audit per day
                for ($date = $from->copy(); $date->lte($to); $date->addDay()) {
                    $audits[] = $this->createAuditInstance($data, $date, $date);
                }
            } elseif ($period === 'weekly') {
                // Create audits every 7 days with range
                $start = $from->copy();
                while ($start->lte($to)) {
                    $end = $start->copy()->addDays(6);
                    if ($end->gt($to)) $end = $to->copy();

                    $audits[] = $this->createAuditInstance($data, $start, $end);
                    $start = $end->copy()->addDay();
                }
            } elseif ($period === 'monthly') {
                // Create audits every 30 days with range
                $start = $from->copy();
                while ($start->lte($to)) {
                    $end = $start->copy()->addDays(29);
                    if ($end->gt($to)) $end = $to->copy();

                    $audits[] = $this->createAuditInstance($data, $start, $end);
                    $start = $end->copy()->addDay();
                }
            }

            return $audits;
        }

        $audits = Audit::create($data);
        $audits->refresh();
        $audits->status_translated = $this->translateStatus($audits->status, $lang);

        // For surprise audits → create a single record
        return $audits;
    }
    public function translateStatus($status, $lang)
    {
        $translations = [
            'draft'         => ['ar' => 'مسودة',           'en' => 'Draft'],
            'pending'       => ['ar' => 'قيد الانتظار',     'en' => 'Pending'],
            'being_audited' => ['ar' => 'قيد المراجعة',      'en' => 'Being Audited'],
            'Done'          => ['ar' => 'منتهي',            'en' => 'Done'],
        ];

        return $translations[$status][$lang] ?? $status;
    }
    /**
     * Helper function to create an audit instance with adjusted audit_date.
     */
    protected function createAuditInstance($data, Carbon $from, Carbon $to)
    {
        $auditData = $data;
        $auditData['from_date'] = $from->format('Y-m-d');
        $auditData['to_date'] = $to->format('Y-m-d');
        $auditData['audit_date'] = $from->format('Y-m-d'); // can represent start date

        // Ensure each repeated audit has a unique number
        $latestId = Audit::max('id') + 1;
        $auditData['audit_number'] = str_pad($latestId, 5, '0', STR_PAD_LEFT);

        return Audit::create($auditData);
    }
    public function addItems($auditId, $items)
    {
        $audit = Audit::find($auditId);

        foreach ($items as $item) {
            $productBrandId = $item['product_brand_id'];
            $barcode = $item['barcode'];

            // Find existing audit item by audit_id + product_brand_id + barcode
            $existingItem = AuditItem::where('audit_id', $audit->id)
                ->where('product_brand_id', $productBrandId)
                ->where('barcode', $barcode)
                ->first();

            // Get system quantity from helper or fallback
            $systemQty = getProductQuantity($productBrandId, 'base', $barcode);

            if ($existingItem) {
                // Update existing item
                $existingItem->update([
                    'unit_id' => $item['unit_id'] ?? $existingItem->unit_id,
                    'system_qty' => $systemQty,
                    'actual_qty' => $item['actual_qty'] ?? $existingItem->actual_qty,
                    'reason_id' => $item['reason_id'] ?? $existingItem->reason_id,
                    'modified_by' => authActionSave()['by'],
                    'modified_by_type' => authActionSave()['type'],
                ]);
            } else {
                // Create new item
                AuditItem::create([
                    'audit_id' => $audit->id,
                    'product_brand_id' => $productBrandId,
                    'barcode' => $barcode,
                    'unit_id' => $item['unit_id'] ?? null,
                    'system_qty' => $systemQty,
                    'actual_qty' => $item['actual_qty'] ?? $systemQty,
                    'reason_id' => $item['reason_id'] ?? null,
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]);
            }
        }

        // Return audit with fresh items data
        return $audit->load([
            'items.productBrand.product',
            'items.productBrand.brand',
            'items.reason'
        ]);
    }



    public function getAuditProducts($id)
    {
        $audit = Audit::find($id);

        $scopeType = $audit->scope_type; // e.g. 'zone', 'location', 'category', 'product', 'branch', 'store'
        $scopeIds = json_decode($audit->scope_ids, true);


        $query = ProductBrand::query()->with(
            'productStores',
            'productStores.store',
            'product'
        );

        // 🧭 Handle each scope type
        switch ($scopeType) {
            case 'zone':
                $query->whereHas('productStores.zone', function ($q) use ($scopeIds) {
                    $q->whereIn('zones.id', $scopeIds);
                });
                break;

            case 'location':
                $query->whereHas('productStores.storageLocation', function ($q) use ($scopeIds) {
                    $q->whereIn('storage_locations.id', $scopeIds);
                });
                break;

            case 'category':
                $query->whereHas('product.category', function ($q) use ($scopeIds) {
                    $q->whereIn('categories.id', $scopeIds);
                });
                break;

            case 'product':
                $query->whereIn('id', $scopeIds);
                break;

            case 'branch':
                $query->whereHas('productStores.store', function ($q) use ($scopeIds) {
                    $q->whereIn('stores.id', $scopeIds);
                });
                break;

            case 'store':
                $query->whereHas('productStores', function ($q) use ($scopeIds) {
                    $q->whereIn('store_id', $scopeIds);
                });
                break;

            default:
                return [];
        }
        $products = $query->get()->map(function ($productBrand) {
            return [
                'id' => $productBrand->id,
                'name' => $productBrand->product->name,
            ];
        });
        return  $products;
    }




    public function changeStatus($id, $status)
    {
        $audit = Audit::find($id);

        $audit->status = $status;

        $audit->updated_by = authActionSave()['by'];
        $audit->save();


        return $audit->fresh();
    }
    public function UpdateStock($audit)
    {
        foreach ($audit->items as $item) {
            $productBrand = ProductBrand::find($item->product_brand_id);
            $ProductTransaction = ProductTransaction::where('product_brand_id', $item->product_brand_id)
                ->where('barcode', $item->barcode)
                ->orderBy('id', 'desc')
                ->first();
            if ($productBrand && $ProductTransaction) {
                storeProductTransaction(
                    $productBrand->id,
                    $item->actual_qty - $item->system_qty,
                    $item->unit_id,
                    $item->barcode,
                    $ProductTransaction->production_date,
                    $ProductTransaction->expiration_date,
                    'out',
                    authActionSave()['by'],
                    'audit_items',
                    $audit->id
                );
            }
        }
    }


    public function update($id, $data)
    {
        $audit = Audit::findOrFail($id);

        if (isset($data['scope_ids']) && is_array($data['scope_ids'])) {
            $data['scope_ids'] = json_encode($data['scope_ids']);
        }

        $data['modified_by'] = authActionSave()['by'];
        $data['modified_by_type'] = authActionSave()['type'];

        $audit->update($data);

        return $audit->fresh();
    }

    public function destroy($id)
    {
        $audit = Audit::findOrFail($id);
        $audit->deleted_by = authActionSave()['by'];
        // $audit->deleted_by_type = authActionSave()['type'];
        $audit->save();

        $audit->delete();
        return true;
    }
}
