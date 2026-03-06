<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DelayTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang', 'ar');
        if ($lang == 'ar') {
            $type = $this->type == 1 ? 'دقائق' : 'ساعات';
        }else{
            $type = $this->type == 1 ? 'minutes' : 'hours';
        }

        return [
            'id' => $this->id,
            'time' => $this->time . ' ' .$type,
            'punishment' => $this->{$lang === 'en' ? 'punishment_en' : 'punishment_ar'}
        ];
    }
}
