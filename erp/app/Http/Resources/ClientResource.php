<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'email'     => $this->email,
            'country_code' => $this->country_code,
            'phone'     => $this->phone,
            'birth_date'=> $this->birth_date,
            'active'    => (bool) $this->is_active,
            'country'  => new CountryResource($this->whenLoaded('country')),
            'addresses'=> AddressResource::collection($this->whenLoaded('activeAddresses')),
        ];
    }
}
