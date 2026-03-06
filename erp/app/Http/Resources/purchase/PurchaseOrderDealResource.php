<?php

namespace App\Http\Resources\purchase;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderDealResource extends JsonResource
{
    use Paginatable;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */

    protected function translateStatus(?string $status): array
    {
        $map = [
            'pending'   => __('purchase/translate.Pending'),
            'accept_pm' => __('purchase/translate.Accepted by PM'),
            'accept_fm' => __('purchase/translate.Accepted by FM'),
            'rejected'  => __('purchase/translate.Rejected'),
        ];
        return [
            'key'   => $status,
            'value' => $map[$status] ?? $status,
        ];
    }

    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'en');

        // Ensure we have a collection of deals
        $allDeals = $this->whenLoaded('deals');
        $allDeals = $allDeals instanceof \Illuminate\Support\Collection ? $allDeals : collect($allDeals ?? []);

        // Minimum price among all deals
        $minPrice = $allDeals->min(fn($deal) => $deal->priceDeal->price ?? PHP_INT_MAX);

        // Minimum period among all deals
        $minPeriod = $allDeals->min(fn($deal) => $deal->priceDeal->period ?? PHP_INT_MAX);

        return [
            'id' => $this->id,

            'status' => $this->translateStatus($this->status),

            'price_deal' => $this->whenLoaded('priceDeal', function () use ($lang) {
                return [
                    'id'     => $this->priceDeal->id,
                    'name'   => $lang === 'ar'
                        ? $this->priceDeal->name_ar
                        : $this->priceDeal->name_en,
                    'price'  => $this->priceDeal->total_after_negotiated ?? null,
                    'period' => $this->priceDeal->period ?? null,
                    'vendor' => new VendorBasicResource($this->priceDeal->vendor),
                    'note'=>$this->priceDeal->note ?? null,
                ];
            }),

            'min_price'     => $this->priceDeal && $this->priceDeal->price === $minPrice,
            'fast_delivery' => $this->priceDeal && $this->priceDeal->period === $minPeriod,

            'created_at' => formatDateTime($this->created_at, $this->lang),
            'updated_at' => formatDateTime($this->updated_at, $this->lang),
        ];
    }
}
