<?php

namespace App\Listeners;

use App\Events\ProductTransactionEvent;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductLimit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\ProductTransaction;
use App\Models\ProductTransactionLog;
use App\Models\User;
use App\Models\Setting;
// use Auth;
use Illuminate\Support\Facades\Auth;

class AddProductTransactionEvent
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ProductTransactionEvent $event): void
    {
        $product = $event->product;
        $today = date('Y-m-d');
        $now_product_count = 0;
        $product_count = $product->count;
        $user_id = Auth::guard('api')->user()->id;
        $order_id = $product->order_id;
        $order_details_id = $product->order_details_id;
        $transaction_type = $product->transaction_type;
        $order_type = $product->order_type;

        //$type == 1
        if ($product->type == 1) {
            //$all_product_transactions = ProductTransaction::query()->with('products.productUnites')->where('product_id', $product->product_id)->where('store_id', $product->store_id)->where('count', '>', 0)->whereDate('expired_date', '>=', $today)->get();

            $all_product_transactions = ProductTransaction::query()->where('product_id', $product->product_id)->where('store_id', $product->store_id)->where('count', '>', 0)->whereDate('expired_date', '>=', $today);
            $all_product_transactions = $all_product_transactions->select('*');
            $all_product_transactions = $all_product_transactions->with('products.productUnites');

            $check_setting = Setting::where('id', 1)->first();
            if ($check_setting) {
                //if($check_setting->stock_transfer_method === 'fifo'){
                if ($check_setting->stock_transfer_method === 'First In First Out') {
                    $all_product_transactions = $all_product_transactions->orderBy('id', 'asc')->get();
                } else {
                    $all_product_transactions = $all_product_transactions->orderBy('id', 'desc')->get();
                }
            }

            if ($all_product_transactions) {
                foreach ($all_product_transactions as $all_product_transaction) {
                    $now_product_count = $all_product_transaction->count - ($product_count * $all_product_transaction->products->productUnites->factor);

                    if ($now_product_count <= 0) {
                        $product_transaction_count = 0;
                    } else {
                        $product_transaction_count = $now_product_count;
                    }

                    if ($now_product_count < 0) {
                        $product_count = ($product_count * $all_product_transaction->products->productUnites->factor) - $all_product_transaction->count;
                        $product_count = $product_count / $all_product_transaction->products->productUnites->factor;
                    }

                    $update_product_transaction = ProductTransaction::find($all_product_transaction->id);
                    $update_product_transaction->count = $product_transaction_count;
                    $update_product_transaction->modified_by = $user_id;
                    $update_product_transaction->save();

                    //add product transaction log
                    $count = ($product_count * $all_product_transaction->products->productUnites->factor);
                    $this->add_product_transaction_log($all_product_transaction->id, $product->type, 'edit', $count, $user_id, $order_id, $order_details_id, $transaction_type, $order_type);
                    //end of product transaction log

                    if ($now_product_count >= 0) {
                        $this->checkProductStock($product->product_id, $user_id);
                        break;
                    }
                }
            }

            //end of $type == 1
        } else { //if $type == 2
            $update_product_transaction = ProductTransaction::with('products.productUnites')->where('product_id', $product->product_id)->where('store_id', $product->store_id)->whereDate('expired_date', date($product->expired_date))->first();
            if ($update_product_transaction) {
                $now_product_count = $update_product_transaction->count + ($product_count * $update_product_transaction->products->productUnites->factor);
                $update_product_transaction->count = $now_product_count;
                $update_product_transaction->modified_by = $user_id;
                $update_product_transaction->save();


                //add product transaction log
                $count = ($product_count * $update_product_transaction->products->productUnites->factor);
                $this->add_product_transaction_log($update_product_transaction->id, $product->type, 'edit', $count, $user_id, $order_id, $order_details_id, $transaction_type, $order_type);
                //end of product transaction log

                $this->checkProductStock($product->product_id, $user_id);
            } else {
                $show_product = Product::with('productUnites')->where('id', $product->product_id)->first();
                $add_product_transaction = new ProductTransaction();
                $add_product_transaction->product_id = $product->product_id;
                $add_product_transaction->store_id = $product->store_id;
                $add_product_transaction->count = ($product_count * $show_product->productUnites->factor);
                $add_product_transaction->expired_date = $product->expired_date;
                $add_product_transaction->created_by = $user_id;
                $add_product_transaction->save();

                //add product transaction log
                $count = ($product_count * $show_product->productUnites->factor);
                $this->add_product_transaction_log($add_product_transaction->id, $product->type, 'add', $count, $user_id, $order_id, $order_details_id, $transaction_type, $order_type);
                //end of product transaction log

                $this->checkProductStock($product->product_id, $user_id);
            }
        }
        //end of $type == 2

    }

    private function checkProductStock($productId, $userId)
    {
        $currentStock = ProductTransaction::where('product_id', $productId)->sum('count');
        $productLimit = ProductLimit::where('product_id', $productId)->first();

        // If current stock is less than min_limit, send a notification
        if ($productLimit && $currentStock < $productLimit->min_limit) {

            $product = Product::find($productId);
            $users = User::where('flag', 0)->get();

            foreach ($users as $user) {

                Notification::create([
                    'title_ar' => 'المنتج أقل من الكمية المحددة',
                    'title_en' => 'Product Below Minimum Limit',
                    'type' => 'stock_alert',
                    'description_ar' => "الكمية المتاحة للمنتج {$product->name_ar} أقل من الحد الأدنى المطلوب.",
                    'description_en' => "The available quantity of product {$product->name_en} is below the required minimum limit.",
                    'status' => 0, // Unread
                    'user_id' => $user->id,
                    'created_by' => $userId, // The user who triggered this event
                    'product_id' => $productId,
                    'date_time' => now(),
                ]);
            }
        }
    }

    private function add_product_transaction_log($product_transaction_id, $type, $model_action, $count, $created_by, $order_id = null, $order_details_id = null, $transaction_type = null, $order_type=null)
    {
        //add product transaction log
        $get_product_transaction = ProductTransaction::where('id', $product_transaction_id)->first();
        $add_product_transaction_log = new ProductTransactionLog();
        $add_product_transaction_log->type = $type;
        $add_product_transaction_log->product_id = $get_product_transaction->product_id;
        $add_product_transaction_log->store_id = $get_product_transaction->store_id;
        $add_product_transaction_log->product_size_id = $get_product_transaction->product_size_id;
        $add_product_transaction_log->product_color_id = $get_product_transaction->product_color_id;
        $add_product_transaction_log->count = $count;
        $add_product_transaction_log->expired_date = $get_product_transaction->expired_date;
        $add_product_transaction_log->model_name = $model_action;
        $add_product_transaction_log->created_by = $created_by;
        if ($order_id != null) {
            $add_product_transaction_log->order_id = $order_id;
        }
        if ($order_details_id != null) {
            $add_product_transaction_log->order_details_id = $order_details_id;
        }
        if ($transaction_type != null) {
            $add_product_transaction_log->transaction_type = $transaction_type;
        }
        if ($order_type != null) {
            $add_product_transaction_log->order_type = $order_type;
        }
        $add_product_transaction_log->save();
        //end of product transaction log
    }
}
