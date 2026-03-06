<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\PaymentInterval;
use App\Models\Vendor;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentIntervalResource extends ResourceCollection
{
    private $lang;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }

    public function toArray($request)
    {
        return $this->collection->map(function ($paymentInterval) {
            return $this->formatPaymentIntervalResponse($paymentInterval);
        })->toArray();
    }

    private function formatPaymentIntervalResponse($paymentInterval)
    {
        $isArray = is_array($paymentInterval);
        if ($isArray) {
            $vendorObjects = [];
            if (!empty($paymentInterval['vendor_ids'])) {
                $vendors = Vendor::whereIn('id', $paymentInterval['vendor_ids'])->get();
                $vendorObjects = $vendors->map(function ($vendor) {
                    return [
                        'id' => $vendor->id,
                        'name' => $vendor->name,
                    ];
                });
            }
        } else {
            $vendorObjects = $paymentInterval->vendors()->get()->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                ];
            });
        }
        // Prepare payment interval data
        $paymentIntervalData = [
            'id' => $isArray ? $paymentInterval['id'] : $paymentInterval->id,
            'name' => $this->getTranslatedField($paymentInterval, 'name'),
            'name_ar' => $isArray ? $paymentInterval['name_ar'] : $paymentInterval->name_ar,
            'name_en' => $isArray ? $paymentInterval['name_en'] : $paymentInterval->name_en,
            'number_of_days' => (int) ($isArray ? $paymentInterval['number_of_days'] : $paymentInterval->number_of_days),
            'status' => (int) ($isArray ? $paymentInterval['status'] : $paymentInterval->status),
            'all_vendors' => (int) ($isArray ? $paymentInterval['all_vendors'] : $paymentInterval->all_vendors),
            'vendor_ids' =>$vendorObjects,
            'vendors_count' => $isArray ?
                (isset($paymentInterval['vendors_count']) ? $paymentInterval['vendors_count'] : 0) :
                $paymentInterval->vendors()->count(),
            'created_at' => formatDateTime($isArray ? $paymentInterval['created_at'] : $paymentInterval->created_at, $this->lang),
            'updated_at' => formatDateTime($isArray ? $paymentInterval['updated_at'] : $paymentInterval->updated_at, $this->lang),

            'created_by' => $this->getEmployeeName($isArray ? ($paymentInterval['created_by'] ?? null) : $paymentInterval->createdBy),
            'modified_by' => $this->getEmployeeName($isArray ? ($paymentInterval['modified_by'] ?? null) : $paymentInterval->modifiedBy),
        ];

        return $paymentIntervalData;
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
