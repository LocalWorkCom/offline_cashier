<?php

namespace App\Services\ClientServices;

use App\Models\Order;
use App\Models\OrderAddon;
use App\Models\OrderDetail;
use App\Models\OrderWaste;
use App\Traits\OrderWasteLogTrait;
use Illuminate\Support\Facades\DB;

class WasteService
{
    use OrderWasteLogTrait;

    public function storeWaste(array $data, string $lang, $employeeId)
    {
        $order = Order::find($data['order_id']);

        // if ($order->status === 'completed') {
        //     return  respondErrorData( $lang === 'en' ? 'Error.' : 'حدث خطأ', 400, [$lang === 'en' ? 'Order completed.' : 'الطلب مكتمل بالفعل.']);
        //     // return RespondWithBadRequestWithData(['error' => $lang === 'en' ? 'Order completed.' : 'الطلب مكتمل بالفعل.']);
        // }

        DB::beginTransaction();

        try {
       
            $waste = new OrderWaste();
            $waste->order_id = $order->id;
             
            $waste->invoice_id = $data['invoice_id'];
            $waste->return_invoice_request_id = $data['return_invoice_request_id'];
            $waste->waste_quantity = (int)$data['waste_quantity'];
            $waste->waste_reason_id = (int)$data['waste_reason_id'];
            $waste->type = $data['type'];
            $waste->flag = $data['flag'];
            $waste->reused = (int)$data['reused'];
            $waste->note = $data['note'] ?? null;
            $waste->created_by = $employeeId;

            if ($data['flag'] === 'dish') {
                $orderDetail = OrderDetail::find($data['order_detail_id']);
                $waste->order_detail_id = $orderDetail->id;
                $waste->original_quantity = (int)$orderDetail->quantity;
            } else {
                
                $orderAddon = OrderAddon::find($data['order_addon_id']);
            
                $waste->order_detail_id = $orderAddon->id;
                $waste->original_quantity = (int)$orderAddon->quantity;
            }

            $waste->save();
            $this->logWasteAction(
                $waste,
                'create',
                null,
                null,
                $lang == 'en' ? 'Waste record created for dish' : 'تم إنشاء سجل هدر للطبق'
            );
            
            DB::commit();
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            DB::rollBack();
                        return  respondErrorData( $lang === 'en' ? 'Error.' : 'حدث خطأ',400, [$lang === 'en' ? 'Order completed.' : 'الطلب مكتمل بالفعل.']);

            // return RespondWithBadRequestWithData(['Exception: ' . $e->getMessage()]);
        }
    }
}
