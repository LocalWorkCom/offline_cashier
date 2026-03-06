<?php

namespace App\Services\StoreServices;

class StoreInventoryService
{
    public static function getStoreInventory($lang, $transactions, $storeId)
    {
        $store = $transactions->first()->stores;
        $storeName = $lang == 'en' ? $store->name_en : $store->name_ar;

        $incomingTotal = 0;
        $outgoingTotal = 0;
        $inventoryDetails = [];

        foreach ($transactions as $transaction) {
            $transactionDetails = $transaction->allStoreTransactionDetails;
            foreach ($transactionDetails as $detail) {
                $product = $detail->products;

                $transactionData = [
                    'productId' => $detail->product_id,
                    'productName' => $lang == 'en' ? $product->name_en : $product->name_ar,
                    'count' => $detail->count,
                    'price' => $detail->price,
                    'totalPrice' => $detail->total_price,
                    'date' => $transaction->date,
                ];

            if ($transaction->type == 1) {
                    $outgoingTotal += $detail->count;
                    $transactionData['transactionType'] = 'outgoing';
            }else if ($transaction->type == 2) {
                $incomingTotal += $detail->count;
                $transactionData['transactionType'] = 'incoming';
            }
                $inventoryDetails[] = $transactionData;
            }
        }

        $currentBalance = $incomingTotal - $outgoingTotal;

        return [
            'storeId' => $storeId,
            'storeName' => $storeName,
            'incomingTotal' => $incomingTotal,
            'outgoingTotal' => $outgoingTotal,
            'currentBalance' => $currentBalance,
            'inventoryDetails' => $inventoryDetails,
        ];
    }
}
