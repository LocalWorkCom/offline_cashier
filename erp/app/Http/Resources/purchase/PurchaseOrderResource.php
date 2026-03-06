<?php

namespace App\Http\Resources\purchase;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
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

       $translateEnum = function ($value, $type) {
            $translations = [
                'priority' => [
                    'Low'    => __('purchase/translate.Low'),
                    'Medium' => __('purchase/translate.Medium'),
                    'High'   => __('purchase/translate.High'),
                    'Urgent' => __('purchase/translate.Urgent'),
                ],
                'type' => [
                    'direct'   => __('purchase/translate.Direct'),
                    'indirect' => __('purchase/translate.Indirect'),
                ],
                'status' => [
                    'draft'      => __('purchase/translate.Draft'),
                    'submitted'  => __('purchase/translate.Submitted'),
                    'accept_po'  => __('purchase/translate.Accepted PO'),
                    'accept_fm'  => __('purchase/translate.Accepted FM'),
                    'accepted'   => __('purchase/translate.Accepted'),
                    'rejected'   => __('purchase/translate.Rejected'),
                ],
                'type_po' => [
                    'pr_linked' => __('purchase/translate.PR Linked'),
                    'unlinked'  => __('purchase/translate.Unlinked'),
                ],
            ];

            return [
                'key'   => $value,
                'value' => $translations[$type][$value] ?? $value,
            ];
        };

        return [
            'id'                  => $this->id,
            'po_number'           => $this->po_number,
            'type_po'             => $translateEnum($this->type_po, 'type_po'),
            'pr'                  => $this->whenLoaded('purchaseRequest', function () {
                return [
                    'id'   => $this->purchaseRequest->id,
                    'name' => $this->purchaseRequest->pr_number ?? null,
                ];
            }),

            'to_department'       => $this->whenLoaded('toDepartment', function () {
                return [
                    'id'   => $this->toDepartment->id,
                    'name' => $this->toDepartment->name ?? null,
                ];
            }),
            'employee'            => $this->whenLoaded('employee', function () {
                return [
                    'id'   => $this->employee->id,
                    'name' => $this->employee->name ?? null,
                ];
            }),
            'category'            => $this->whenLoaded('category', function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name ?? null,
                ];
            }),
            'address'             => $this->address,
            'lat'                 => $this->lat,
            'long'                => $this->long,
            'type'                => $translateEnum($this->type, 'type'),
            'priority'            => $translateEnum($this->priority, 'priority'),
            'arraival_date'       => $this->arraival_date,
            'period'              => $this->period,
            'total'               => $this->total,
            'note_delivery'       => $this->note_delivery,
            'note'                => $this->note,
            'pm_approval'         => $this->whenLoaded('pmApproval', function () {
                return [
                    'id'   => $this->pmApproval->id,
                    'name' => $this->pmApproval->name ?? null,
                ];
            }),
            'fm_approval'         => $this->whenLoaded('fmApproval', function () {
                return [
                    'id'   => $this->fmApproval->id,
                    'name' => $this->fmApproval->name ?? null,
                ];
            }),
            'status'              => $translateEnum($this->status, 'status'),
            'rejected_at'         => $this->rejected_at,
            'rejected_from'       => $this->rejected_from,
            'rejected_by'         => $this->whenLoaded('rejectedBy', function () {
                return [
                    'id'   => $this->rejectedBy->id,
                    'name' => $this->rejectedBy->name ?? null,
                ];
            }),
            'reject_reason'       => $this->whenLoaded('rejectReason', function () {
                return [
                    'id'   => $this->rejectReason->id,
                    'name' => $this->rejectReason->name ?? null,
                ];
            }),
            'employee' => $this->whenLoaded('employee', function () {
        return [
            'id'   => $this->employee->id,
            'name' => $this->employee->first_name .' '. $this->employee->last_name ?? null,
            'employee_code' => $this->employee->employee_code ?? null,
        ];
    }),
            'items'               => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'deals'               => PurchaseOrderDealResource::collection($this->whenLoaded('deals')),
            'created_at'          =>formatDateTime( $this->created_at, $this->lang),
            'updated_at'          =>formatDateTime($this->updated_at, $this->lang) ,
        ];
    }
}
