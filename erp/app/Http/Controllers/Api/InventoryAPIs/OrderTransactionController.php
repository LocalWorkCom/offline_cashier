<?php

namespace App\Http\Controllers\Api\InventoryAPIs;

use App\Http\Controllers\Controller;

use App\Models\Order;
use App\Models\OrderTracking;
use App\Models\OrderTransaction;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class OrderTransactionController extends Controller
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
        $order_id = $request->order_id;
        $orderTransactions = OrderTransaction::where('order_id', $order_id)->get();

        return ResponseWithSuccessData($lang, $orderTransactions, 1);
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');  // Default to 'ar' if not provided

        // Check if token is valid
        if (!CheckToken()) {
            return RespondWithBadRequest($lang, 5);
        }

        // Check if the authenticated user is allowed to proceed
        if (Auth::guard('api')->user()->flag == 0) {
            return RespondWithBadRequest($lang, 5);
        } else {
            $created_by = Auth::guard('api')->user()->id;
        }

        // Validation rules
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|exists:orders,id', // Ensures order exists
            'payment_method' => 'required|string|in:cash,credit_card',  // Enforce enum-like values
            'paid' => 'required|numeric|min:0', // Paid amount must be numeric and non-negative
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return RespondWithBadRequestWithData($validator->errors());
        }

        // Fetch the order
        $order_id = $request->order_id;
        $order = Order::find($order_id);

        $transactionId = Str::uuid()->toString(); // Generate unique transaction ID
        $done = false;
        $points_num = 0;

        // Create a new order transaction
        $order_transaction = new OrderTransaction();
        $order_transaction->order_id = $order_id;
        $order_transaction->payment_method = $request->payment_method;
        $order_transaction->transaction_id = $transactionId;
        $order_transaction->created_by = $created_by;
        $order_transaction->paid = $request->paid;
        $order_transaction->date = date('Y-m-d');
        $order_transaction->discount_id = $order->discount_id;
        $order_transaction->coupon_id = $order->coupon_id;
        $order_transaction->save();

        // Check if the paid amount is enough to complete the payment
        if ($request->paid >= $order->total_price_after_tax) {
            $done = true;
        }

        // Update the payment status based on the paid amount
        if ($order_transaction && $done) {

            $order_transaction->payment_status = "paid";
            $order_transaction->paid_at = date('Y-m-d H:i:s');
          
            // $order_tracking = new OrderTracking();
            // $order_tracking->order_id = $order_id;
            // $order_tracking->status = 'in_progress';
            // $order_tracking->save();
          
            // call point function  total = $order->total_price_after_tax   && $request->payment_method == cash && $order->type == online
            if($request->payment_method == 'cash' && isValid($order->branch_id )){
                if(isActive($order->branch_id ) ){
                    $points_num =  calculateEarnPoint($order->total_price_after_tax,$order->branch_id , $order_id , $order->client_id);
                }
            }
        } else {
            $order_transaction->points_num = $points_num;
            $order_transaction->payment_status = "unpaid";
        }

        $order_transaction->save();

        return RespondWithSuccessRequest($lang, 1);
    }
}
