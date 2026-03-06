<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\StoreTransaction;
use App\Models\StoreTransactionDetails;
use App\Models\ProductTransactionLog;
use App\Models\Store;
use App\Models\OrderRefund;
use App\Models\OrderProduct;
use App\Models\OrderDetail;
use App\Models\OrderAddon;
use App\Models\Setting;
use App\Models\ProductUnit;
use App\Events\ProductTransactionEvent;
use Illuminate\Support\Facades\Auth;

trait StoreTransactionTrait
{
    /*public function add_item_tostore($data=array()) { //order_type -> 1 add order, 2 add purchase, 3 refund order, 4 refund purchase
        switch($order_type)
        {
            case 1:
                $this->add_order_to_store($order_id, $order_type);
                break;
            case 2:
                $this->add_purchase_to_store($order_id, $order_type);
                break;
            case 3:
                $this->refund_order_to_store($order_id, $order_type);
                break;
            default:
            $this->refund_purchase_to_store($order_id, $order_type);

        }
    } */

    public function handel_order_to_store($order_id)
    {
        $total_price = 0;
        $price = 0;
        $user_id = Auth::guard('api')->user()->id;

        $order = Order::with('orderDetails', 'orderAddons')->where('id', $order_id)->first();
        $store = Store::with('branch')->where('branch_id', $order->branch_id)->where('is_kitchen', 1)->first();

        $order_array = [];
        $products = [];
        if ($order) {

            //recipes
            if($order->orderDetails){
                foreach($order->orderDetails as $order_details){
                    $order_quantity = $order_details->quantity;
                    //return $order_details->dishes->recipes->recipe->ingredients;
                    if($order_details->dishes){
                        if($order_details->dishes->recipes){
                            foreach($order_details->dishes->recipes as $recipe_details)
                            {
                                if($recipe_details->recipe->ingredients){
                                    foreach($recipe_details->recipe->ingredients as $ingredients){
                                        $product_unit = ProductUnit::where('id', $ingredients->product_unit_id)->first();
                                        $product = array(
                                            "product_id" => $ingredients->product_id,
                                            "product_unit_id" => $ingredients->product_unit_id,
                                            "product_size_id" => null,
                                            "product_color_id" => null,
                                            "country_id" => $ingredients->product->currency_code,
                                            "count" => ($ingredients->quantity * $order_quantity) / $product_unit->factor,
                                            "expired_date" => null,
                                            "order_id" => $order_id,
                                            "order_details_id" => $order_details->id,
                                            "transaction_type" => 1,
                                            "order_type" => 2
                                        );
                                        array_push($products, $product);
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //products
            if($order->orderProducts){
                foreach($order->orderProducts as $order_products){
                    $product_unit = ProductUnit::where('id', $order_products->product_unit_id)->first();
                    $product = array(
                        "product_id" => $order_products->product_id,
                        "product_unit_id" => $order_products->product_unit_id,
                        "product_size_id" => null,
                        "product_color_id" => null,
                        "country_id" => $order_products->products->currency_code,
                        "count" => $order_products->quantity / $product_unit->factor,
                        "expired_date" => null,
                        "order_id" => $order_id,
                        "order_details_id" => $order_products->id,
                        "transaction_type" => 1,
                        "order_type" => 1
                    );
                    array_push($products, $product);
                }
            }

            //addons
            if($order->orderAddons){
                foreach($order->orderAddons as $order_addons){
                $order_quantity = $order_addons->quantity;
                    if($order_addons->Addon){
                        if($order_addons->Addon->recipe){
                            if($order_addons->Addon->recipe->ingredients){
                                foreach($order_addons->Addon->recipe->ingredients as $ingredient){
                                    $product_unit = ProductUnit::where('id', $ingredients->product_unit_id)->first();
                                    $product = array(
                                        "product_id" => $ingredient->product_id,
                                        "product_unit_id" => $ingredient->product_unit_id,
                                        "product_size_id" => null,
                                        "product_color_id" => null,
                                        "country_id" => $ingredient->product->currency_code,
                                        "count" => ($ingredient->quantity * $order_quantity) / $product_unit->factor,
                                        "expired_date" => null,
                                        "order_id" => $order_id,
                                        "order_details_id" => $order_addons->id,
                                        "transaction_type" => 1,
                                        "order_type" => 3
                                    );
                                    array_push($products, $product);
                                }
                            }
                        }
                    }
                }
            }


            $order_array['type'] = 1;
            $order_array['to_type'] = 2;
            $order_array['to_id'] = $order->client_id;
            $order_array['date'] = $order->date;
            $order_array['store_id'] = $store->id;
            $order_array['user_id'] = $order->created_by;
            $order_array['created_by'] = $order->created_by;
            $order_array['products'] = $products;

        }

        return $order_array;
    }

    public function refund_order_to_store($order_id) {
        $total_price = 0;
        $price = 0;
        $user_id = Auth::guard('api')->user()->id;

        $order_refunds = OrderRefund::where('order_id', $order_id)->get();
        $order_array = [];
        $products = [];

        $order_details_branch = Order::where('id', $order_id)->first();
        $store = Store::with('branch')->where('branch_id', $order_details_branch->branch_id)->where('is_kitchen', 1)->first();

        if($store){

            if ($order_refunds) {
                foreach($order_refunds as $order){

                    //dish
                    if($order->item_type == "dish"){
                        $order_quantity = $order->quantity;
                        $order_details = OrderDetail::with('dishes')->where('id', $order->item_id)->first();
                        if($order_details->dishes){
                            if($order_details->dishes->recipes){
                                foreach($order_details->dishes->recipes as $recipe_details)
                                {
                                    $order_quantity = $order_quantity * $recipe_details->quantity;
                                    if($recipe_details->recipe->ingredients){
                                        foreach($recipe_details->recipe->ingredients as $ingredients){
                                            //get expired date
                                            $check_product_transaction_log = ProductTransactionLog::where('product_id', $ingredients->product_id)->where('order_id', $order_id)->where('order_details_id', $order_details->id)->where('order_type', 2)->first();
                                            //end of get expired date

                                            $product_unit = ProductUnit::where('id', $ingredients->product_unit_id)->first();
                                            $product = array(
                                                "product_id" => $ingredients->product_id,
                                                "product_unit_id" => $ingredients->product_unit_id,
                                                "product_size_id" => null,
                                                "product_color_id" => null,
                                                "country_id" => $ingredients->product->currency_code,
                                                "count" => ($ingredients->quantity * $order_quantity) / $product_unit->factor,
                                                "expired_date" => $check_product_transaction_log->expired_date,
                                                "order_id" => $order_id,
                                                "order_details_id" => $order_details->id,
                                                "transaction_type" => 2,
                                                "order_type" => 2
                                            );
                                            array_push($products, $product);
                                        }
                                    }
                                }
                            }
                        }
                    }

                    //products
                    if($order->item_type == "product"){
                        $order_quantity = $order->quantity;
                        $order_product = OrderProduct::where('id', $order->item_id)->first();
                        $product_unit = ProductUnit::where('id', $order_product->product_unit_id)->first();
                        //get expired date
                        $check_product_transaction_log = ProductTransactionLog::where('product_id', $order_product->product_id)->where('order_id', $order_id)->where('order_details_id', $order_product->id)->where('order_type', 1)->first();
                        //end of get expired date
                        $product = array(
                            "product_id" => $order_product->product_id,
                            "product_unit_id" => $order_product->product_unit_id,
                            "product_size_id" => null,
                            "product_color_id" => null,
                            "country_id" => $order_product->products->currency_code,
                            "count" => $order_quantity / $product_unit->factor,
                            "expired_date" => $check_product_transaction_log->expired_date,
                            "order_id" => $order_id,
                            "order_details_id" => $order_product->id,
                            "transaction_type" => 2,
                            "order_type" => 1
                        );
                        array_push($products, $product);
                    }

                    if($order->item_type == "addon"){
                        $order_quantity = $order->quantity;
                        $order_addon = OrderAddon::with('Addon')->where('id', $order->item_id)->first();
                        if($order_addon->Addon){
                            if($order_addon->Addon->recipe){
                                if($order_addon->Addon->recipe->ingredients){
                                    foreach($order_addon->Addon->recipe->ingredients as $ingredient){
                                        $product_unit = ProductUnit::where('id', $ingredient->product_unit_id)->first();
                                        //get expired date
                                        $check_product_transaction_log = ProductTransactionLog::where('product_id', $ingredient->product_id)->where('order_id', $order_id)->where('order_details_id', $order_addon->id)->where('order_type', 3)->first();
                                        //end of get expired date
                                        $product = array(
                                            "product_id" => $ingredient->product_id,
                                            "product_unit_id" => $ingredient->product_unit_id,
                                            "product_size_id" => null,
                                            "product_color_id" => null,
                                            "country_id" => $ingredient->product->currency_code,
                                            "count" => ($ingredient->quantity * $order_quantity) / $product_unit->factor,
                                            "expired_date" => $check_product_transaction_log->expired_date,
                                            "order_id" => $order_id,
                                            "order_details_id" => $order_addon->id,
                                            "transaction_type" => 2,
                                            "order_type" => 3
                                        );
                                        array_push($products, $product);
                                    }
                                }
                            }
                        }
                    }
                }



                $order_array['type'] = 2;
                $order_array['to_type'] = 1;
                $order_array['to_id'] = $order_details_branch->client_id;
                $order_array['date'] = $order_details_branch->date;
                $order_array['store_id'] = $store->id;
                $order_array['user_id'] = $order_details_branch->created_by;
                $order_array['created_by'] = $order_details_branch->created_by;
                $order_array['products'] = $products;

            }

        }
        return $order_array;
    }

    public function add_item_to_store($data = array()) {
        $price = 0;
        $total_price = 0;
        $user_id = Auth::guard('api')->user()->id;
        $setting = Setting::where('id', 1)->first();

        if($setting->withdrawal_store == 1){

            $add_store_bill = new StoreTransaction();
            $add_store_bill->type = $data['type'];
            $add_store_bill->to_type = $data['to_type'];
            $add_store_bill->to_id = $data['to_id'];
            $add_store_bill->date = $data['date'];
            $add_store_bill->total = count($data['products']);
            $add_store_bill->store_id = $data['store_id'];
            $add_store_bill->user_id = $data['user_id'];
            $add_store_bill->created_by = $data['created_by'];
            $add_store_bill->total_price = $total_price;
            $add_store_bill->save();

            $store_transaction_id = $add_store_bill->id;

            foreach($data['products'] as $product)
            {

                $add_store_items = new StoreTransactionDetails();
                $add_store_items->store_transaction_id = $store_transaction_id;
                $add_store_items->product_id = $product['product_id'];
                $add_store_items->product_unit_id = $product['product_unit_id'];
                $add_store_items->product_size_id = $product['product_size_id'];
                $add_store_items->product_color_id = $product['product_color_id'];
                $add_store_items->country_id = $product['country_id'];
                $add_store_items->count = $product['count'];
                $add_store_items->price = $price;
                $add_store_items->total_price = $total_price;
                $add_store_items->save();
                $add_store_items->type = $add_store_bill->type;
                $add_store_items->to_type = $add_store_bill->to_type;
                $add_store_items->user_id = $add_store_bill->user_id;
                $add_store_items->store_id = $data['store_id'];
                $add_store_items->expired_date = $product['expired_date'];
                $add_store_items->order_id = $product['order_id'];
                $add_store_items->order_details_id = $product['order_details_id'];
                $add_store_items->transaction_type = $product['transaction_type'];
                $add_store_items->order_type = $product['order_type'];

                event(new ProductTransactionEvent($add_store_items));
            }

        }
        return $stores = $add_store_bill;
    }

}
