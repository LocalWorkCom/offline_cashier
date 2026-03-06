<?php

namespace App\Services\StoreServices;

use App\Models\Category;

class CategoryInventoryService
{
    public static function getCategoryInventory($lang, $transactions, $categoryId)
    {
        $productIds = $transactions->pluck('product_id')->unique();
        $category = Category::find($categoryId);
        $categoryName = $lang === 'en' ? $category->name_en : $category->name_ar;

        $inventoryData = [];

        foreach ($productIds as $productId) {
            $productTransactions = $transactions->where('product_id', $productId);

            if ($productTransactions->isEmpty()) {
                continue;
            }

            $firstTransaction = $productTransactions->first();
            $product = $firstTransaction->products;
            $productName = $lang === 'en' ? $product->name_en : $product->name_ar;

            $beginningBalance = $firstTransaction->count;
            $incomingTotal = 0;
            $outgoingTotal = 0;
            $previousCount = 0;

            $incomingTransactions = [];
            $outgoingTransactions = [];

            foreach ($productTransactions as $transaction) {
                $currentCount = $transaction->count;
                $date = $transaction->created_at;

                if ($previousCount < $currentCount) {
                    $incomingAmount = $currentCount - $previousCount;
                    $incomingTotal += $incomingAmount;
                    $incomingTransactions[] = [
                        'amount' => $incomingAmount,
                        'date' => $date
                    ];
                } elseif ($previousCount > $currentCount) {
                    $outgoingAmount = $previousCount - $currentCount;
                    $outgoingTotal += $outgoingAmount;
                    $outgoingTransactions[] = [
                        'amount' => $outgoingAmount,
                        'date' => $date
                    ];
                }
                $previousCount = $currentCount;
            }

            $balance = $incomingTotal - $outgoingTotal;

            $inventoryData[] = [
                'categoryId' => $category->id,
                'categoryName' => $categoryName,
                'productId' => $productId,
                'productName' => $productName,
                'beginningBalance' => $beginningBalance,
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
        }

        return $inventoryData;
    }


}
