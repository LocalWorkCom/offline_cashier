<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SupplyOrderResource extends JsonResource
{
    use Paginatable;

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'en');
        // Status translation
        $statusTranslations = [
            'en' => [
                'draft'     => 'Draft',
                'submitted' => 'Submitted',
                'approved'  => 'Approved',
                'rejected'  => 'Rejected',
            ],
            'ar' => [
                'draft'     => 'مسودة',
                'submitted' => 'تم الإرسال',
                'approved'  => 'تمت الموافقة',
                'rejected'  => 'مرفوض',
            ],
        ];

        // Pick translated status based on language
        $translatedStatus = $statusTranslations[$lang][$this->status] ?? $this->status;
        return [
            'id'          => $this->id,
            'so_number'      => $this->order_number,
            'date' => Carbon::parse($this->date)->format('Y-m-d'),

            'time' => $lang == 'ar'
                ? Carbon::parse($this->date)->format('h:i') . ' ' .
                (Carbon::parse($this->date)->format('A') == 'AM' ? 'صباحًا' : 'مساءً')
                : Carbon::parse($this->date)->format('h:i A'),
            'type'           => $this->type,
            'supply_reason' =>  [
                'id' => $this->supply_reason_id,
                'name' => optional($this->reason)->name,
            ],
            'status' => [
                'key' => $this->status,
                'name' => $translatedStatus,
            ],

            'from_store' => $this->fromStore ? [
                'id'   => $this->fromStore->id,
                'name' => $this->fromStore->name,
            ] : null,

            'to_store' => $this->toStore ? [
                'id'   => $this->toStore->id,
                'name' => $this->toStore->name,
            ] : null,
            'items' => SupplyOrderItemResource::collection($this->items),

        ];
    }
}
