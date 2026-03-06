<?php

namespace App\Http\Resources\Inventory;

use App\Traits\Paginatable;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DspResource extends JsonResource
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

        return [
            'id'             => $this->id,
            'dsp_no'         => $this->dsp_no,
            'date' => Carbon::parse($this->date)->format('Y-m-d'),

            'time' => $lang == 'ar'
                ? Carbon::parse($this->date)->format('h:i') . ' ' .
                (Carbon::parse($this->date)->format('A') == 'AM' ? 'صباحًا' : 'مساءً')
                : Carbon::parse($this->date)->format('h:i A'),
            'department'     => $this->department,
            'dsp_status'  => $this->status ? [
                'id'   => $this->status->id,
                'name' => $this->status->name,
            ] : null,

            'from_store' => $this->fromStore ? [
                'id'   => $this->fromStore->id,
                'name' => $this->fromStore->name,
            ] : null,

            'to_store' => $this->toStore ? [
                'id'   => $this->toStore->id,
                'name' => $this->toStore->name,
            ] : null,

            'qa_tester' => $this->qaTester ? [
                'id'   => $this->qaTester->id,
                'name' => trim($this->qaTester->first_name . ' ' . $this->qaTester->last_name),
            ] : null,

            'purchase_request' => $this->purchaseRequest ? [
                'id'         => $this->purchaseRequest->id,
                'pr_number'  => $this->purchaseRequest->pr_number,
            ] : null,

            'supply_order' => $this->supplyOrder ? [
                'id'        => $this->supplyOrder->id,
                'so_number' => $this->supplyOrder->so_number,
            ] : null,
            'items' => DspItemResource::collection($this->items),
        ];
    }
}
