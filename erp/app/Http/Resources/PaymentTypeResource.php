<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use App\Models\PaymentType;
use App\Models\Vendor;
use Illuminate\Http\Resources\Json\ResourceCollection;

class PaymentTypeResource extends ResourceCollection
{
    private $lang;

    public function __construct($resource, $lang = 'en')
    {
        parent::__construct($resource);
        $this->lang = $lang;
    }
    private function translatedType($type)
    {
        $translations = [
            'Deposit Billing'   => ['en' => 'Deposit Billing',   'ar' => 'فاتورة الدفعة المقدمة'],
            'Without Deposit'   => ['en' => 'Without Deposit',   'ar' => 'بدون دفعة مقدمة'],
            'Advanced Payment'  => ['en' => 'Advanced Payment',  'ar' => 'دفعة مقدمة'],
            'Deferred Payment'  => ['en' => 'Deferred Payment',  'ar' => 'دفعة مؤجلة'],
            'Periodic Payment'  => ['en' => 'Periodic Payment',  'ar' => 'دفعات دورية'],
        ];

        return $translations[$type][$this->lang] ?? $type;
    }

    public function toArray($request)
    {
        return $this->collection->map(function ($paymentType) {
            return $this->formatPaymentTypeResponse($paymentType);
        })->toArray();
    }

    private function formatPaymentTypeResponse($paymentType)
    {
        $isArray = is_array($paymentType);
        if ($isArray) {
            $vendorObjects = [];
            if (!empty($paymentInterval['vendor_ids'])) {
                $vendors = Vendor::whereIn('id', $paymentType['vendor_ids'])->get();
                $vendorObjects = $vendors->map(function ($vendor) {
                    return [
                        'id' => $vendor->id,
                        'name' => $vendor->name,
                    ];
                });
            }
        } else {
            $vendorObjects = $paymentType->vendors()->get()->map(function ($vendor) {
                return [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                ];
            });
        }
        // Get payment interval data
        $paymentIntervalData = $this->getPaymentIntervalData($paymentType);

        // Prepare payment type data
        $paymentTypeData = [
            'id' => $isArray ? $paymentType['id'] : $paymentType->id,
            'type' => [
                'key' => $isArray ? $paymentType['type'] : $paymentType->type,
                'value' => $this->translatedType($isArray ? $paymentType['type'] : $paymentType->type),
            ],
            'name' => $this->getTranslatedField($paymentType, 'name'),
            'name_ar' => $isArray ? $paymentType['name_ar'] : $paymentType->name_ar,
            'name_en' => $isArray ? $paymentType['name_en'] : $paymentType->name_en,
            'description' => $this->getTranslatedField($paymentType, 'description'),
            'description_ar' => $isArray ? $paymentType['description_ar'] : $paymentType->description_ar,
            'description_en' => $isArray ? $paymentType['description_en'] : $paymentType->description_en,
            'deposit' => (int) ($isArray ? $paymentType['deposit'] : $paymentType->deposit),
            'max_delay_percent' => $isArray ? $paymentType['max_delay_percent'] : $paymentType->max_delay_percent,
            'status' => (int) ($isArray ? $paymentType['status'] : $paymentType->status),
            'all_vendors' => (int) ($isArray ? $paymentType['all_vendors'] : $paymentType->all_vendors),
            'vendor_ids' => $vendorObjects,
            'vendors_count' => $isArray ?
                (isset($paymentType['vendors_count']) ? $paymentType['vendors_count'] : 0) :
                $paymentType->vendors()->count(),
            'payment_interval' => $paymentIntervalData,
            'created_at' => formatDateTime($isArray ? $paymentType['created_at'] : $paymentType->created_at, $this->lang),
            'updated_at' => formatDateTime($isArray ? $paymentType['updated_at'] : $paymentType->updated_at, $this->lang),
            'created_by' => $this->getEmployeeName($isArray ? ($paymentType['created_by'] ?? null) : $paymentType->createdBy),
            'modified_by' => $this->getEmployeeName($isArray ? ($paymentType['modified_by'] ?? null) : $paymentType->modifiedBy),
        ];

        return $paymentTypeData;
    }

    private function getPaymentIntervalData($paymentType)
    {
        $isArray = is_array($paymentType);

        if ($isArray) {
            $paymentInterval = $paymentType['payment_interval'] ?? null;
            if (!$paymentInterval) {
                return null;
            }
            return [
                'id' => $paymentInterval['id'] ?? null,
                'name_ar' => $paymentInterval['name_ar'] ?? null,
                'name_en' => $paymentInterval['name_en'] ?? null,
                'name' => $this->lang == 'en' ? ($paymentInterval['name_en'] ?? null) : ($paymentInterval['name_ar'] ?? null),
                'number_of_days' => $paymentInterval['number_of_days'] ?? null,
            ];
        } else {
            $paymentInterval = $paymentType->paymentInterval;
            if (!$paymentInterval) {
                return null;
            }
            return [
                'id' => $paymentInterval->id,
                'name_ar' => $paymentInterval->name_ar,
                'name_en' => $paymentInterval->name_en,
                'name' => $this->lang == 'en' ? $paymentInterval->name_en : $paymentInterval->name_ar,
                'number_of_days' => $paymentInterval->number_of_days,
            ];
        }
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
