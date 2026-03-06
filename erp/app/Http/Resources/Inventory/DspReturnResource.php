<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DspReturnResource extends JsonResource
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
            'id'             => $this->id,
            // 'dsp'         => $this->dsp_id ? [
            //     'id' => $this->dsp_id,
            //     'dsp_no' => $this->dsp->dsp_no
            // ] : null,
            'return_code' => $this->return_code ?? null,

            'date' => Carbon::parse($this->created_at)->format('Y-m-d'),

            'time' => $lang == 'ar'
                ? Carbon::parse($this->created_at)->format('h:i') . ' ' .
                (Carbon::parse($this->created_at)->format('A') == 'AM' ? 'صباحًا' : 'مساءً')
                : Carbon::parse($this->created_at)->format('h:i A'),
            'reason'  => $this->reason_id ? [
                'id'   => $this->reason->id,
                'name' => $this->reason->name,
            ] : null,

            'status' => [
                'key' => $this->status,
                'name' => $translatedStatus,
            ],
            'note' => $this->note ?? null,
            'returned_quantity' => $this->returned_quantity ?? null,
            'documents' => $this->documents ?? null,
            'dsp' => new DspResource($this->whenLoaded('dsp')),
        ];
    }
}
