<?php

namespace App\Traits;

use App\Models\ProductTransaction;
use Illuminate\Support\Facades\DB;

trait ProductCheck
{
    /*public function check_expirt($transaction_products, $type, $to_type) {
        $today = date('Y-m-d');
        $all_product_not_expirt = [];
        $xx = [];
        foreach($transaction_products as $check_product)
        {
            //check if outgoing and product is expirt date
            if($type == 1)
            {
                if($to_type == 2 || $to_type == 4)
                {
                    $product_details = ProductTransaction::where('product_id', $check_product['product_id'])->whereDate('expired_date','>=',$today)->get()->pluck('product_id')->toArray();
                    $xx = array_merge($all_product_not_expirt,$product_details);
                }
            }
        }

        return $xx;
    }*/

    public function check_expirt($transaction_products, $type, $store_id)
    {
        $today = date('Y-m-d');
        $all_product_not_expirt = 0;

        foreach ($transaction_products as $check_product) {
            //check if outgoing and product is expirt date
            if ($type == 1) {
                $product_details = ProductTransaction::where('product_id', $check_product['product_id'])->where('store_id', $store_id)->where('count', '>', 0)->whereDate('expired_date', '>=', $today)->first();
                if (isset($product_details)) {
                    $all_product_not_expirt = 1;
                }

                if ($all_product_not_expirt == 0) {
                    return $all_product_not_expirt;
                }
            }
        }
        return $all_product_not_expirt;
    }

    public function check_not_enough($transaction_products, $type, $store_id)
    {
        $today = date('Y-m-d');
        $all_product_not_enough = 0;
        foreach ($transaction_products as $check_product) {
            //check if outgoing and product is expirt date
            if ($type == 1) {
                $today = date('Y-m-d');
                $product_details = ProductTransaction::query()->where('product_id', $check_product['product_id'])->where('store_id', $store_id)->where('count', '>', 0)->whereDate('expired_date', '>=', $today);
                $product_details = $product_details->select('product_id', DB::raw('sum(count) as total_count'));
                $product_details = $product_details->with(['products', 'products.unites', 'products.sizes', 'products.colors', 'products.productUnites'])->groupBy('product_id')->first();

                if (isset($product_details) && $product_details->total_count > ($check_product['count'] * $product_details->products->productUnites->factor)) {
                    $all_product_not_enough = 1;
                }

                if ($all_product_not_enough == 0) {
                    return $all_product_not_enough;
                }
            }
        }
        return $all_product_not_enough;
    }

    public function check_product_instore($transaction_products, $store_id)
    {
        $today = date('Y-m-d');
        $all_product_not_instore = 0;
        foreach ($transaction_products as $check_product) {
            //check if outgoing and product is expirt date
            $today = date('Y-m-d');
            $product_details = ProductTransaction::query()->where('product_id', $check_product['product_id'])->where('store_id', $store_id)->where('count', '>', 0)->whereDate('expired_date', '>=', $today);
            $product_details = $product_details->select('product_id', DB::raw('sum(count) as total_count'));
            $product_details = $product_details->with(['products', 'products.unites', 'products.sizes', 'products.colors', 'products.productUnites'])->groupBy('product_id')->first();

            if (isset($product_details)) {
                $all_product_not_instore = 1;
            }

            if ($all_product_not_instore == 0) {
                return $all_product_not_instore;
            }
        }

        return $all_product_not_instore;
    }
}
