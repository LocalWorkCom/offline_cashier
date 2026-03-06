<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentSequenceResource  extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       return [
            'id' => $this->id,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'document_type_id' => $this->document_type_id,
            'numbering_style' => $this->numbering_style,
            'format' => $this->format,
            'include_year' => $this->include_year,
            'year_format' => $this->year_format,
            'include_branch' => $this->include_branch,
            'branch_id' => $this->branch_id,
            'active' => $this->active,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'format_details' => $this->formats->map(fn($format) => [
                'id' => $format->id,
                'logo_location' => $format->logo_location,
                'header_text_en' => $format->header_text_en,
                'header_text_ar' => $format->header_text_ar,
                'footer_text_en' => $format->footer_text_en,
                'footer_text_ar' => $format->footer_text_ar,
                'font_type' => $format->font_type,
                'font_size' => $format->font_size,
                'compliance_text_en' => $format->compliance_text_en,
                'compliance_text_ar' => $format->compliance_text_ar,
                'signeter_count' => $format->signeter_count,
                'signeters' => $format->signeters->map(fn($s) => [
                    'id' => $s->id,
                    'name_en' => $s->name_en,
                    'name_ar' => $s->name_ar,
                    'position' => $s->position,
                ]),
            ]),
        ];
    }
}
