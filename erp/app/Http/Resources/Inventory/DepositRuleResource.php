<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DepositRuleResource extends JsonResource
{
    use Paginatable;
    private $lang;

    public function __construct($resource, $lang = 'ar')
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

    /**
     * Translate vendor_type enum.
     */
    private function translateVendorType($type)
    {
        $translations = [
            'individual'   => ['en' => 'Individual', 'ar' => 'فردي'],
            'company'      => ['en' => 'Company', 'ar' => 'شركة'],
            'local_market' => ['en' => 'Local Market', 'ar' => 'السوق المحلي'],
        ];

        return [
            'key'   => $type,
            'value' => $translations[$type][$this->lang] ?? $type,
        ];
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
            'name'=>$this->name,
            'name_en'        => $this->name_en,
            'name_ar' => $this->name_ar,
            'percentage'  => $this->percentage,
            'applicable_to' => $this->applicable_to,
            'vendor_type' => $this->translateVendorType($this->vendor_type), // translated
            'conditions'  => $this->conditions,
            'linked_high_value_rule' => $this->highValueRule ? [
                'id'   => $this->highValueRule->id,
                'name' => $this->highValueRule->name,
            ] : null,
            'active'      => $this->active,
            'created_at'  => formatDateTime($this->created_at, $this->lang),
            'updated_at'  => formatDateTime($this->updated_at, $this->lang),
            'vendors'     => $this->vendors->map(function ($vendor) {
                return [
                    'id'   => $vendor->id,
                    'name' => $vendor->name,
                ];
            }),
        ];
    }
}
