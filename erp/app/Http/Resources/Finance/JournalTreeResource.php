<?php

namespace App\Http\Resources\Finance;

use Illuminate\Http\Resources\Json\JsonResource;

class JournalTreeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'parent_id' => $this->parent_id,
            'description' => $this->description,
            'is_payment_account' => $this->is_payment_account,
            'is_active' => $this->is_active,
            'open_balance' => $this->open_balance,
            'balance' => $this->balance,
            'children' => $this->when($this->children->isNotEmpty(),
                fn() => JournalTreeResource::collection($this->children)),
            'has_children' => $this->children->isNotEmpty(),
            'facilities' => JournalFacilitiesResource::collection($this->facilities)
        ];
    }
}
