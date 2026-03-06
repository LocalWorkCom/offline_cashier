<?php

namespace App\Http\Resources;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOrderCancelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        $lang = $request->header('lang') ?? 'ar';
        return [
            'delivery' => [
                'id'           => $this['delivery']->id,
                'full_name'    => trim($this['delivery']->first_name . ' ' . $this['delivery']->last_name),
                'phone_number' => $this['delivery']->phone_number,
                'orders_count' => $this['orders']->count(),
            ],
            'orders' => $this['orders']->map(function ($order) use ($lang) {
                return [
                    'id'            => $order->id,
                    'order_number'  => $order->order_number,
                    'status'        => $order->status,
                    'date'          => $order->date,
                    'created_at'    => $order->created_at,
                    'deliveryComplaints' => [
                        'id'      => $order->deliveryComplaints?->id,
                        'status'  => $order->deliveryComplaints?->status,
                        'message' => $order->deliveryComplaints?->message,
                    ],
                    'cancellationReasons' => $order->cancellationReasons->map(function ($reason) use ($lang) {
                        return [
                            'id'     => $reason->id,
                            'reason' => $reason->reasonModel?->{'reason_' . $lang} ?? null,
                        ];
                    }),
                    'branch' => [
                        'id'          => $order->branch?->id,
                        'name'        => $order->branch?->{'name' . $lang},
                        'address'     => $order->branch?->{'address_' . $lang},
                        'phone_number' => $order->branch?->phone_number,
                        'email'       => $order->branch?->email,
                    ],
                ];
            }),
        ];
    }


}
