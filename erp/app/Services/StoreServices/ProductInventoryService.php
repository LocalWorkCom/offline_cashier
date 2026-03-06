<?php

namespace App\Services\StoreServices;

class ProductInventoryService
{
    public static function getProductInventory($lang, $transactions, $productId)
    {
        $product = $transactions->first()->products;
        $productName = $lang === 'en' ? $product->name_en : $product->name_ar;

        $beginningBalance = $transactions->first()->count;
        $incomingTotal = 0;
        $outgoingTotal = 0;
        $previousCount = 0;

        $incomingTransactions = [];
        $outgoingTransactions = [];

        foreach ($transactions as $transaction) {
            $currentCount = $transaction->count;
            $date = $transaction->created_at;

            if ($previousCount < $currentCount) {
                $incomingAmount = $currentCount - $previousCount;
                $incomingTotal += $incomingAmount; //$incomingTotal = $incomingTotal + $incomingAmount;
                $incomingTransactions[] = [
                    'amount' => $incomingAmount,
                    'date' => $date
                ];
            } elseif ($previousCount > $currentCount) {
                $outgoingAmount = $previousCount - $currentCount;
                $outgoingTotal += $outgoingAmount; //$outgoingTotal = $outgoingTotal + $outgoingAmount;
                $outgoingTransactions[] = [
                    'amount' => $outgoingAmount,
                    'date' => $date
                ];
            }
            $previousCount = $currentCount;
        }

        $balance = $incomingTotal - $outgoingTotal;

        // data returned
        $inventoryData = [
            'productId' => $productId,
            'productName' => $productName,
            'beginningBalance'=> $beginningBalance,
            'incoming' => [
                'total' => $incomingTotal,
                'transactions' => $incomingTransactions,
            ],
            'outgoing' => [
                'total' => $outgoingTotal,
                'transactions' => $outgoingTransactions,
            ],
            'currentBalance' => $balance,
        ];
        return $inventoryData;
    }

}
