<?php

namespace App\Traits;

use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\KitchenLog;

trait KitchenLogTrait
{
    //add to first time
    public function add_order_in_kitchen($order_id)
    {
        try {
            $get_order = Order::where('id', $order_id)->with('orderDetails', 'orderAddons')->first();
            if ($get_order) {
                if ($get_order->orderDetails) {
                    foreach ($get_order->orderDetails as $order_details) {
                        KitchenLog::create([
                            'order_id' => $get_order->id,
                            'order_details_id' => $order_details->id,
                            'dish_id' => $order_details->dish_id,
                            'dish_size_id' => $order_details->dish_size_id,
                            'offer_id' => $order_details->offer_id,
                            'quantity' => $order_details->quantity,
                            'status' => "pending",
                            'dish_order' => $order_details->dish_order,
                            'date' => date('Y-m-d'),
                            'time' => date('H:i:s'),
                            'branch_id' => $get_order->branch_id,
                            'table_id' => $get_order->table_id,
                            'order_type' => $get_order->type,
                            'created_by' => Auth()->user()->id,
                        ]);
                    }
                }

                if ($get_order->orderAddons) {
                    foreach($get_order->orderAddons as $order_addon) {
                        KitchenLog::create([
                            'order_id' => $get_order->id,
                            'order_details_id' => $order_addon->order_details_id,
                            'dish_id' => $order_addon->orderDetails->dish_id,
                            'dish_addon_id' => $order_addon->dish_addon_id,
                            'order_addone_id' => $order_addon->id,
                            'quantity' => $order_addon->quantity,
                            'status' => "pending",
                            'dish_order' => $order_addon->orderDetails->dish_order,
                            'date' => date('Y-m-d'),
                            'time' => date('H:i:s'),
                            'branch_id' => $get_order->branch_id,
                            'table_id' => $get_order->table_id,
                            'order_type' => $get_order->type,
                            'created_by' => Auth()->user()->id,
                        ]);
                    }
                }


                //return back()->withSuccess(__('messages.success'));
            } else {
                return back()->withErrors([__('messages.forbidden')]);
            }
        } catch (\Exception $e) {
            return back()->withErrors([__('messages.forbidden')]);
        }

    }

    //change status in kitchen
    public function change_status_in_kitchen($order_details_id, $status)
    {
        try{
            $order_details = OrderDetail::where('id', $order_details_id)->with('dishAddons')->first();
            if ($order_details) {
                    KitchenLog::create([
                        'order_id' => $order_details->order_id,
                        'order_details_id' => $order_details->id,
                        'dish_id' => $order_details->dish_id,
                        'dish_size_id' => $order_details->dish_size_id,
                        'offer_id' => $order_details->offer_id,
                        'quantity' => $order_details->quantity,
                        'status' => $status,
                        'dish_order' => $order_details->dish_order,
                        'date' => date('Y-m-d'),
                        'time' => date('H:i:s'),
                        'branch_id' => $order_details->Order->branch_id,
                        'table_id' => $order_details->Order->table_id,
                        'order_type' => $order_details->Order->type,
                        'created_by' => Auth()->user()->id,
                    ]);

                if ($order_details->dishAddons) {
                    foreach($order_details->dishAddons as $order_addon) {
                        KitchenLog::create([
                            'order_id' => $order_addon->order_id,
                            'order_details_id' => $order_addon->order_details_id,
                            'dish_id' => $order_addon->orderDetails->dish_id,
                            'dish_addon_id' => $order_addon->dish_addon_id,
                            'order_addone_id' => $order_addon->id,
                            'quantity' => $order_addon->quantity,
                            'status' => $status,
                            'dish_order' => $order_addon->orderDetails->dish_order,
                            'date' => date('Y-m-d'),
                            'time' => date('H:i:s'),
                            'branch_id' => $order_details->Order->branch_id,
                            'table_id' => $order_details->Order->table_id,
                            'order_type' => $order_details->Order->type,
                            'created_by' => Auth()->user()->id,
                        ]);
                    }
                }

            } else {
                return back()->withErrors([__('messages.forbidden')]);
            }
        } catch (\Exception $e) {
            return back()->withErrors([__('messages.forbidden')]);
        }
    }

}
