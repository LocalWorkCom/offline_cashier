<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\PaymentMethod;
use App\Models\Vendor;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentMethodResource extends ResourceCollection
{
    private $lang;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }

    public function toArray($request)
    {
        return $this->collection->map(function ($paymentMethod) {
            return $this->formatPaymentMethodResponse($paymentMethod);
        })->toArray();
    }

    private function formatPaymentMethodResponse($paymentMethod)
    {
        $isArray = is_array($paymentMethod);
        if ($isArray) {
            $vendorObjects = [];
            if (!empty($paymentInterval['vendor_ids'])) {
                $vendors = Vendor::whereIn('id', $paymentMethod['vendor_ids'])->get();
                $vendorObjects = $vendors->map(function ($vendor) {
                    return [
                        'id' => $vendor->id,
                        'name' => $vendor->name,
                    ];
                });
            }
        } else {
            $vendorObjects = $paymentMethod->vendors()->get()->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                ];
            });
        }
        // Prepare payment method data
        $paymentMethodData = [
            'id' => $isArray ? $paymentMethod['id'] : $paymentMethod->id,
            'type' => $isArray ? $paymentMethod['type'] : $paymentMethod->type,
            'name' => $this->getTranslatedField($paymentMethod, 'name'),
            'name_ar' => $isArray ? $paymentMethod['name_ar'] : $paymentMethod->name_ar,
            'name_en' => $isArray ? $paymentMethod['name_en'] : $paymentMethod->name_en,
            'description' => $this->getTranslatedField($paymentMethod, 'description'),
            'description_ar' => $isArray ? $paymentMethod['description_ar'] : $paymentMethod->description_ar,
            'description_en' => $isArray ? $paymentMethod['description_en'] : $paymentMethod->description_en,
            'additional_info' => $isArray ? $paymentMethod['additional_info'] : $paymentMethod->additional_info,
            'status' => (int) ($isArray ? $paymentMethod['status'] : $paymentMethod->status),
            'all_vendors' => (int) ($isArray ? $paymentMethod['all_vendors'] : $paymentMethod->all_vendors),
            'vendor_ids' =>$vendorObjects,
            'vendors_count' => $isArray ?
                (isset($paymentMethod['vendors_count']) ? $paymentMethod['vendors_count'] : 0) :
                $paymentMethod->vendors()->count(),
            'created_at' => formatDateTime($isArray ? $paymentMethod['created_at'] : $paymentMethod->created_at, $this->lang),
            'updated_at' => formatDateTime($isArray ? $paymentMethod['updated_at'] : $paymentMethod->updated_at, $this->lang),
            'created_by' => $this->getEmployeeName($isArray ? ($paymentMethod['created_by'] ?? null) : $paymentMethod->createdBy),
            'modified_by' => $this->getEmployeeName($isArray ? ($paymentMethod['modified_by'] ?? null) : $paymentMethod->modifiedBy),
        ];

        return $paymentMethodData;
    }

    private function getTranslatedField($item, $field)
    {
        $isArray = is_array($item);
        $fieldAr = $field . '_ar';
        $fieldEn = $field . '_en';

        if ($isArray) {
            return $this->lang == 'en' && !empty($item[$fieldEn]) ? $item[$fieldEn] : $item[$fieldAr];
        } else {
            return $this->lang == 'en' && !empty($item->$fieldEn) ? $item->$fieldEn : $item->$fieldAr;
        }
    }

    private function formatDateTime($date)
    {
        return $date ? Carbon::parse($date)->format('Y-m-d H:i:s') : null;
    }

    private function getEmployeeName($employee)
    {
        if (!$employee) {
            return null;
        }

        if (is_array($employee)) {
            return trim(($employee['first_name'] ?? '') . ' ' . ($employee['last_name'] ?? ''));
        }

        return trim($employee->first_name . ' ' . $employee->last_name);
    }
}
