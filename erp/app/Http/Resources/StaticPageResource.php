<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StaticPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $request->header('lang') ?? app()->getLocale() ;
        $page = $request->query('page');
        // dd($request);
        if($lang == 'en') {
            $title =$page != 'faqs'? $this->name_en : $this->question_en;
            $description= $page != 'faqs'? $this->description_en : $this->answer_en;
        }
        else{
            $title = $page != 'faqs'? $this->name_ar : $this->question_ar;
            $description= $page != 'faqs'? $this->description_ar : $this->answer_ar;
        }
        return [
            'id' => $this->id,
            'title' => strip_tags($title),
            'description' => strip_tags($description),
        ];
    }
}
