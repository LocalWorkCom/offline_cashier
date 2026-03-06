<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WasteReportItemResource extends JsonResource
{

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'en');
        $quantityData = getProductQuantity($this->product_brand_id, 'base', $this->barcode, null);
        $quantity = is_array($quantityData)
            ? ($quantityData['quantity'] ?? 0)
            : ($quantityData ?? 0);
        return [
            'id' => $this->id,
            'product_brand' => $this->productBrand ? [
                'id'   => $this->product_brand_id,
                'name' => collect([
                    $this->productBrand->product->name ?? null,
                    $this->productBrand->brand->name ?? null,
                ])->filter()->join(' - '),
                'barcode' => [
                    'barcode' =>$this->productBrand->transactions?->first()?->barcode,
                    'quantity' => $quantity,
                ],
            ] : null,

            'unit' => $this->unit ? [
                'id' => $this->unit_id,
                'name' => $this->unit?->name ?? '',
            ] : null,

            'waste_reason' => $this->wasteReason ? [
                'id' => $this->waste_reason_id,
                'name' => $this->wasteReason?->name ?? '',
            ] : null,

            'waste_unit' => $this->wasteUnit ? [
                'id' => $this->waste_unit_id,
                'name' => $this->wasteUnit?->name ?? '',
            ] : null,

            'quantity' => (float) $this->quantity,
            'actual_quantity_wasted' => (float) $this->actual_quantity_wasted,

            'status' => [
                'key' => $this->status,
                'name' => match ($this->status) {
                    'pending' => 'معلق',
                    'reviewed' => 'تمت المراجعة',
                    default => 'غير معروف',
                },
            ],

            'image' => $this->image ?? null,

            'reviewed_employee' => $this->reviewedEmployee ? [
                'id' => $this->reviewed_employee_id,
                'name' => $this->reviewedEmployee?->first_name ?? '',
            ] : null,
        ];
    }
}
