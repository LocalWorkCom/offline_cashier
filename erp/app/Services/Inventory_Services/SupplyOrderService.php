<?php

namespace App\Services\Inventory_Services;

use App\Models\Employee;
use App\Models\ProductBrand;
use App\Models\ProductTransaction;
use App\Models\SupplyOrder;
use App\Models\SupplyOrderItem;
use App\Models\SupplyOrderHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class SupplyOrderService
{
    protected $lang;
    public function __construct(Request $request)
    {
        $this->lang = $request->header('lang', 'ar');
    }
    public function createSupplyOrder(array $data): SupplyOrder
    {
        return DB::transaction(function () use ($data) {
            // Create the supply order
            $supplyOrder = SupplyOrder::create([
                'order_number'        => $data['order_number'] ?? 'SO-' . now()->format('Ymd') . '-' . str_pad(SupplyOrder::count() + 1, 4, '0', STR_PAD_LEFT),
                'from_store_id'   => $data['from_store_id'],
                'to_store_id'     => $data['to_store_id'],
                'supply_reason_id'    => $data['supply_reason_id'],
                'type'                => $data['type'],
                'employee_id'             => $data['employee_id'],
                'status'              => $data['status'] ?? 'draft',
                'created_by'         => authActionSave()['by'],
                'created_by_type'         => authActionSave()['type'],
            ]);

            // Create line items
            foreach ($data['items'] as $item) {
                $supplyOrder->items()->create([
                    'product_brand_id'    => $item['product_brand_id'],
                    'quantity'     => $item['quantity'],
                    'unit_id'  => $item['unit_id'],
                    'notes'        => $item['notes'] ?? null,
                    'created_by'         => authActionSave()['by'],
                    'created_by_type'         => authActionSave()['type'],
                ]);
            }
            // Log the creation
            $this->logHistory($supplyOrder, 'Created', 'Supply order created by ' . authActionSave()['by']);

            return $supplyOrder;
        });
    }
    // app/Services/SupplyOrderService.php

    public function update(SupplyOrder $supplyOrder, array $data)
    {


        // Update header fields
        $supplyOrder->update([
            'from_store_id' => $data['from_store_id'],
            'to_store_id' => $data['to_store_id'],
            'supply_reason_id' => $data['supply_reason_id'],
            'type' => $data['type'],
            'updated_by' => authActionSave()['by'],
            'updated_by_type' => authActionSave()['type'],
        ]);

        // Update line items
        // You can choose to delete and recreate, or diff and update
        $supplyOrder->items()->delete(); // simple way: clear old

        foreach ($data['items'] as $itemData) {
            $supplyOrder->items()->create([
                'product_brand_id' => $itemData['product_brand_id'],
                'quantity' => $itemData['quantity'],
                'unit_id'  => $itemData['unit_id'],
                'notes' => $itemData['notes'] ?? null,
            ]);
        }

        // Log history
        $this->logHistory($supplyOrder, 'Updated', 'Order updated by ' . authActionSave()['by']);

        return $supplyOrder;
    }


    public function submitSupplyOrder(SupplyOrder $supplyOrder)
    {
        try {
            return DB::transaction(function () use ($supplyOrder) {


                // Handle periodic type separately
                if ($supplyOrder->type === 'periodic') {
                    // Duplicate the supply order
                    $newOrder = $supplyOrder->replicate(); // clone attributes
                    $newOrder->status = 'submitted';
                    $newOrder->order_number = 'SO-' . strtoupper(Str::random(6));
                    $newOrder->created_at = now();
                    $newOrder->updated_at = now();
                    $newOrder->save();

                    // Duplicate related items
                    if ($supplyOrder->items()->exists()) {
                        foreach ($supplyOrder->items as $item) {
                            $newItem = $item->replicate();
                            $newItem->supply_order_id = $newOrder->id;
                            $newItem->save();
                        }
                    }

                    // Log history for new submitted order
                    $this->logHistory($newOrder, 'Submitted', 'Submitted by ' . authActionSave()['by']);

                    $message = $this->lang == 'en'
                        ? "A submitted copy of the periodic order has been created."
                        : 'تم إنشاء نسخة مقدمة من الطلب الدوري.';


                    return RespondWithSuccessMsg($newOrder);
                }

                // Default behavior for non-periodic orders

                $supplyOrder->update(['status' => 'submitted']);
                $this->logHistory($supplyOrder, 'Submitted', 'Submitted by ' . authActionSave()['by']);

                return ResponseWithSuccessData($this->lang, $supplyOrder, 1);
            });
        } catch (\Exception $e) {
            // Handle any exception that breaks the transaction
            DB::rollBack(); // redundant inside transaction closure, but safe

            $message = $this->lang == 'en'
                ? 'An unexpected error occurred while submitting the order.'
                : 'حدث خطأ غير متوقع أثناء تقديم الطلب.';


            return respondErrorData($message, 500, ['error' => $e->getMessage()]);
        }
    }


    public function logHistory(SupplyOrder $supplyOrder, string $action, string $details): void
    {
        $supplyOrder->history()->create([
            'action' => $action,
            'details' => $details,
        ]);
    }
    // app/Services/SupplyOrderService.php

    public function approve(SupplyOrder $supplyOrder)
    {
        if ($supplyOrder->status !== 'submitted') {
            return [
                'error' => true,
                'message_en' => "Only submitted orders can be approved.",
                'message_ar' => "فقط يمكن الموافقة على الطلبات في حالة المقدمة.",
                'code' => 400
            ];
        }
        $supplyOrder->update(['status' => 'approved']);

        $employee = Employee::with('inventoryStores')->find(auth('employee')->user()->id);
        if ($supplyOrder->from_store_id === $employee->inventoryStores->first()->id) {
            foreach ($supplyOrder->items as $request) {
                $productBrand = ProductBrand::find($request->product_brand_id);
                $options = findBarcodesWithQuantity($request->product_brand_id, $request->quantity, $request->unit_id, true);
                if ($productBrand && $options) {
                    storeProductTransaction(
                        $productBrand->id,
                        $request->quantity,
                        $request->unit_id,
                        $options['barcode'],
                        $options['production_date'],
                        $options['expiration_date'],
                        'out',
                        authActionSave()['by'],
                        'supply_orders',
                        $supplyOrder->id
                    );
                }
            }
        }

        $this->logHistory($supplyOrder, 'Approved', 'Approved by ' . authActionSave()['by']);

        return $supplyOrder;
    }

    public function reject(SupplyOrder $supplyOrder, int $reasonId, ?string $note = null)
    {
        if ($supplyOrder->status !== 'submitted') {
            return [
                'error' => true,
                'message_en' => "Only submitted orders can be rejected.",
                'message_ar' => "فقط يمكن الرفض على الطلبات في حالة المقدمة.",
                'code' => 400
            ];
        }

        $supplyOrder->update([
            'status' => 'rejected',
            'reject_reason_id' => $reasonId,
            'rejection_note' => $note,
        ]);


        $this->logHistory($supplyOrder, 'Rejected', 'Rejected by ' . authActionSave()['by']);
    }
    public function destroy(SupplyOrder $supplyOrder)
    {
        $supplyOrder->deleted_by = authActionSave()['by'];
        $supplyOrder->save();
        $supplyOrder->items()->delete();
        $supplyOrder->delete();

        $this->logHistory($supplyOrder, 'Deleted', 'Deleted by ' . authActionSave()['by']);
    }
}
