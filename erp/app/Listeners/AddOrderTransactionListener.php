<?php

namespace App\Listeners;

use App\Events\OrderTransactionEvent;
use App\Events\ProductTransactionEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Addon;
use App\Models\Recipe;
use App\Models\RecipeAddon;
use App\Models\StoreTransaction;
use App\Models\StoreTransactionDetails;
use App\Models\ProductTransaction;
use App\Models\Store;
use App\Models\Product;
use Auth;

class AddOrderTransactionListener
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
    public function handle(OrderTransactionEvent $event): void
    {
        $tracking_order = $event->order_tracking;
        $total_price = 0;
        $price = 0;
        $user_id = Auth::guard('api')->user()->id;

        $order = Order::with('orderDetails', 'orderAddons')->where('id', $tracking_order->order_id)->first();
        $store = Store::with('branch')->where('branch_id', $order->branch_id)->where('is_kitchen', 1)->first();
        if($order){
                $add_store_bill = new StoreTransaction();
                $add_store_bill->type = 1;
                $add_store_bill->to_type = 2;
                $add_store_bill->to_id = $order->client_id;
                $add_store_bill->date = $order->date;
                $add_store_bill->total = $order->orderDetails->count();
                $add_store_bill->store_id = $store->id;
                $add_store_bill->user_id = $order->client_id;
                $add_store_bill->created_by = $order->created_by;
                $add_store_bill->total_price = $total_price;
                $add_store_bill->save();
                $store_transaction_id = $add_store_bill->id;

            foreach($order->orderDetails as $order_details){
                if($order_details->recipe_id != null){
                    //iuf order is recipe
                    $recipe_details_ingredients = Recipe::with('ingredients')->where('id', $order_details->recipe_id)->first();
                    if($recipe_details_ingredients->ingredients){
                        foreach($recipe_details_ingredients->ingredients as $recipe_details_ingredient){

                            $add_store_items = new StoreTransactionDetails();
                            $add_store_items->store_transaction_id = $store_transaction_id;
                            $add_store_items->product_id = $recipe_details_ingredient->product_id;
                            $add_store_items->product_unit_id = $recipe_details_ingredient->product_unit_id ;
                            $add_store_items->country_id = $store->branch->country_id;
                            $add_store_items->count = ($recipe_details_ingredient->quantity * $order_details->quantity);
                            $add_store_items->price = $price;
                            $add_store_items->total_price = $total_price;
                            $add_store_items->save();
                            $add_store_items->type = 1;
                            $add_store_items->to_type = 2;
                            $add_store_items->user_id = $order->client_id;
                            $add_store_items->store_id = $store->id;
                            $add_store_items->expired_date = "";
                            $add_store_items->order_id = $order->id;
                            $add_store_items->order_details_id = $order_details->id;
                            $add_store_items->order_addon_id = "";

                            event(new ProductTransactionEvent($add_store_items));
                        }
                    }
                    //end of order is recipe
                }else{

                    //if order is product
                    $add_store_items = new StoreTransactionDetails();
                    $add_store_items->store_transaction_id = $store_transaction_id;
                    $add_store_items->product_id = $order_details->product_id;
                    $add_store_items->product_unit_id = $order_details->unit_id;
                    $add_store_items->country_id = $store->branch->country_id;
                    $add_store_items->count = $order_details->quantity;
                    $add_store_items->price = $price;
                    $add_store_items->total_price = $total_price;
                    $add_store_items->save();
                    $add_store_items->type = 1;
                    $add_store_items->to_type = 2;
                    $add_store_items->user_id = $order->client_id;
                    $add_store_items->store_id = $store->id;
                    $add_store_items->expired_date = "";
                    $add_store_items->order_id = $order->id;
                    $add_store_items->order_details_id = $order_details->id;
                    $add_store_items->order_addon_id = "";

                    event(new ProductTransactionEvent($add_store_items));
                    //end if order is poduct
                }   
            }

            if(count($order->orderAddons) > 0){
                foreach($order->orderAddons as $addons_details){

                    $recipe_addons_details = RecipeAddon::findOrFail($addons_details->recipe_addon_id);

                    $add_store_items = new StoreTransactionDetails();
                    $add_store_items->store_transaction_id = $store_transaction_id;
                    $add_store_items->product_id = $recipe_addons_details->product_id;
                    $add_store_items->product_unit_id = $recipe_addons_details->product_unit_id;
                    $add_store_items->country_id = $store->branch->country_id;
                    $add_store_items->count = ($recipe_addons_details->quantity * $addons_details->quantity);
                    $add_store_items->price = $price;
                    $add_store_items->total_price = $total_price;
                    $add_store_items->save();
                    $add_store_items->type = 1;
                    $add_store_items->to_type = 2;
                    $add_store_items->user_id = $order->client_id;
                    $add_store_items->store_id = $store->id;
                    $add_store_items->expired_date = "";
                    $add_store_items->order_id = $order->id;
                    $add_store_items->order_details_id = "";
                    $add_store_items->order_addon_id = $addons_details->id;

                    event(new ProductTransactionEvent($add_store_items));
                }
            }
        }

    }
}
