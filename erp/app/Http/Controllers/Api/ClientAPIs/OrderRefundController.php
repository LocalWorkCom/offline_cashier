<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;

use App\Models\OrderDetail;
use App\Models\OrderRefund;

use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use App\Events\OrderRefundTransactionEvent;
use App\Models\OrderTransaction;
use App\Models\Store;
use Illuminate\Support\Str;


class OrderRefundController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    public function index(Request $request)
    {

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        $orderRefunds = OrderRefund::all();
        foreach ($orderRefunds as $orderRefund) {
            $orderRefund['details'] = OrderDetail::with('Order')->find($orderRefund->order_detail_id);
        }
        return ResponseWithSuccessData($lang, $orderRefunds, 1);
    }
    public function store(Request $request)
    {

        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        if (Auth::guard('api')->user()->flag == 0) {
            return RespondWithBadRequest($lang, 5);
        } else {
            $created_by = Auth::guard('api')->user()->id;
        }
        // Validation
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:255', // Ensure reason is provided, a string, and not too long
            'order_id' => 'required|exists:orders,id', // Ensure the order detail ID exists in the 'order_details' table
            'item_id' => 'required', // Ensure the order detail ID exists in the 'order_details' table
            'item_type' => 'required|in:product,addon,dish', // Ensure the order detail ID exists in the 'order_details' table
            'quantity' => 'required', // Ensure the order detail ID exists in the 'order_details' table
            'price' => 'required', // Ensure the order detail ID exists in the 'order_details' table
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }


        $orderRefund = new OrderRefund();
        $orderRefund->invoice_number = "CN-" . GetNextID("order_refunds") . "-" . rand(1111, 9999); // Generate a new number if it exists
        $orderRefund->date = date('Y-m-d');
        $orderRefund->reason = $request->reason;
        $orderRefund->order_id = $request->order_id;
        $orderRefund->item_id = $request->item_id;
        $orderRefund->item_type = $request->item_type;
        $orderRefund->quantity = $request->quantity;
        $orderRefund->price = $request->price;
        $orderRefund->created_by = $created_by;
        $orderRefund->save();


        return RespondWithSuccessRequest($lang, 1);
    }
    public function change_status(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'en' if not provided
        App::setLocale($lang);

        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }
        if (Auth::guard('api')->user()->flag == 0) {
            return RespondWithBadRequest($lang, 5);
        } else {
            $created_by = Auth::guard('api')->user()->id;
        }

        $order_refund_id = $request->order_refund_id;
        $orderRefund = OrderRefund::find($order_refund_id);
        $orderRefund->status = $request->status;
        $orderRefund->created_by = $created_by;

        $orderRefund->save();


        if ($orderRefund->status == 'accepted') {
            $order_details = OrderDetail::find($orderRefund->order_detail_id);
            $order_details->status = 'refund';
            $order_details->save();
            event(new OrderRefundTransactionEvent($order_details));

            $transactionId = Str::uuid()->toString();


            $order_transaction = new OrderTransaction();
            $order_transaction->order_id = $orderRefund->id;
            $order_transaction->order_type = 'refund';
            $order_transaction->payment_method = 'credit_card';
            $order_transaction->transaction_id = $transactionId;
            $order_transaction->created_by = $created_by;
            $order_transaction->refund =  $orderRefund->price;
            $order_transaction->paid =  0;
            $order_transaction->date = date('Y-m-d');
            $order_transaction->time = date('H:i:s');
            $order_transaction->discount_id =  null;
            $order_transaction->coupon_id = null;
            $order_transaction->payment_status = "paid";
            $order_transaction->paid_at =  date('Y-m-d H:i:s');
            $order_transaction->points_num = 0; //
            $order_transaction->save();
        }

        return RespondWithSuccessRequest($lang, 1);
    }
}
