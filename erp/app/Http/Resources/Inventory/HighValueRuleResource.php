<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HighValueRuleResource extends JsonResource
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
        return [
            'id'          => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'financial_limit' => $this->financial_limit,
            'DRR' => $this->DRR,
            'max_deposit_percentage' => $this->max_deposit_percentage,
            'notes' => $this->notes,
            'active' => $this->active,
            'created_at' => formatDateTime($this->created_at, $this->lang),
            'updated_at' => formatDateTime($this->updated_at, $this->lang),
        ];
    }
}
