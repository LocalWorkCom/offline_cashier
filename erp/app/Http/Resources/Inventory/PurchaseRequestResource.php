<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseRequestResource extends JsonResource
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
        $statusIds = [
            'draft'     => 0,
            'submitted' => 1,
            'approved'  => 2,
            'rejected'  => 3,
        ];
        $translatedStatus = $statusTranslations[$lang][$this->status] ?? $this->status;
        // Base data for inventory module
        $data = [
            'id'         => $this->id,
            'pr_number'  => $this->pr_number,
            'date'       => Carbon::parse($this->pr_date)->format('Y-m-d'),
            'time'       => $lang == 'ar'
                ? Carbon::parse($this->pr_date)->format('h:i') . ' ' .
                (Carbon::parse($this->pr_date)->format('A') == 'AM' ? 'صباحًا' : 'مساءً')
                : Carbon::parse($this->pr_date)->format('h:i A'),

            'status' => [
                'id'   => $statusIds[$this->status] ?? null,

                'key'  => $this->status,
                'name' => $translatedStatus,
            ],
            'period'=>$this->period ?? 0,
            'reason_pr' => $this->reason ? [
                'id'   => $this->reason?->id,
                'name' => $this->reason?->name,
            ] : null,
               'reject_reason_pr' => $this->rejectReason ? [
                'id'   => $this->rejectReason?->id,
                'name' => $this->rejectReason?->name,
            ] : null,
            'items' => PurchaseRequestItemResource::collection($this->whenLoaded('items')),
        ];

        // Additional data for purchase module
        if ($request->attributes->get('module') === 'purchase') {
            $typeTranslations = [
                'en' => [
                    'direct'   => 'Direct',
                    'indirect' => 'Indirect',
                ],
                'ar' => [
                    'direct'   => 'مباشر',
                    'indirect' => 'غير مباشر',
                ],
            ];

            $priorityTranslations = [
                'en' => [
                    'Urgent' => 'Urgent',
                    'High'   => 'High',
                    'Medium' => 'Medium',
                    'Low'    => 'Low',
                ],
                'ar' => [
                    'Urgent' => 'عاجل',
                    'High'   => 'مرتفع',
                    'Medium' => 'متوسط',
                    'Low'    => 'منخفض',
                ],
            ];

            $data = array_merge($data, [
                'employee_name' => $this->employee_name,
                'employee_code' => $this->employee_code,
                'department'    => $this->department ? [
                    'id'   => $this->department->id,
                    'name' => $this->department->name,
                ] : null,
                'type' => [
                    'key'   => $this->type,
                    'value' => $typeTranslations[$lang][$this->type] ?? $this->type,
                ],
                'priority' => [
                    'key'   => $this->priority,
                    'value' => $priorityTranslations[$lang][$this->priority] ?? $this->priority,
                ],
                'receive_date' => $this->receive_date ? Carbon::parse($this->receive_date)->format('Y-m-d') : null,
                'note'         => $this->note,
                'has_new_product' => $this->has_new_product,
                'damy_items' => PurchaseRequestDamyItemResource::collection($this->whenLoaded('damyProducts')),

            ]);
        }
        if ($request->attributes->get('module') === 'inventory') {
            $data = array_merge($data, [
                'vendor'     => $lang == 'ar' ? 'إدارة المشتريات' : 'Procurement Management',
                'store'      => $this->store ? [
                    'id'   => $this->store?->id,
                    'name' => $this->store?->name,
                ] : null,
            ]);
        }
        return $data;
    }
}
