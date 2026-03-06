<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchasingBudgetResource extends JsonResource
{
    use Paginatable;
    private $lang;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $prevAmount = $this->base_amount ?? 0; // default to 0
        $difference = $this->remaining_amount - $prevAmount;
        $flag = $difference > 0 ?__('validation.deposit') : ($difference < 0 ?  __('vaidation.deduct') : __('validation.deposit'));
        return [
            'id'          => $this->id,
            'month'      => $this->month,
            'year' => $this->year,
            'base_amount' => $this->base_amount,
            'increase_amount' => $this->increase_amount,
            'increase_count' => $this->increase_count,
            'remaining_amount' => $this->remaining_amount,
            'notes' => $this->notes,
            'type' =>  $flag,
            'is_active' => $this->is_active,
            'created_by' => $this->createdByUser ? $this->createdByUser->first_name .' '. $this->createdByUser->last_name: null,
            'created_at' => formatDateTime($this->created_at, $this->lang),
            'updated_at' => formatDateTime($this->updated_at, $this->lang),
        ];
    }
}
