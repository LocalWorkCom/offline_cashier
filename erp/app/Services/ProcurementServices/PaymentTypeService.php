<?php

namespace App\Services\ProcurementServices;

use App\Models\PaymentType;
use App\Models\Vendor;
use App\Models\PaymentInterval;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentTypeService
{
    public function index(Request $request)
    {
        $lang = app()->getLocale();

        // Return query builder with vendors count and payment interval relationship
        $query = PaymentType::query()
            ->addSelect([
                'vendors_count' => DB::table('vendors')
                    ->selectRaw('COUNT(*)')
                    ->where(function ($query) {
                        $query->whereRaw('payment_types.all_vendors = 1')
                            ->orWhereRaw('JSON_CONTAINS(payment_types.vendor_ids, JSON_ARRAY(vendors.id))');
                    })
                    ->whereNull('vendors.deleted_at') // Only count non-deleted vendors
            ])
            ->with(['createdBy', 'modifiedBy', 'paymentInterval'])
            ->orderByDesc('updated_at');

        return $query;
    }

    public function store(Request $request)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:Deposit Billing,Without Deposit,Advanced Payment,Deferred Payment,Periodic Payment', 
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_types')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_types')->whereNull('deleted_at')
            ],
            'description_ar' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'deposit' => 'required|integer|min:0|max:100',
            'payment_interval_id' => 'nullable|integer|exists:payment_intervals,id',
            'max_delay_percent' => 'nullable|integer|min:0|max:100',
            'status' => 'required|boolean',
            'all_vendors' => 'required|integer|in:0,1',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'integer|exists:vendors,id',
        ]);

        // Add conditional validation
        $validator->after(function ($validator) use ($request, $lang) {
            $messages = [
                'en' => [
                    'all_vendors_1' => 'Vendor IDs should not be provided when all vendors is selected.',
                    'all_vendors_0' => 'Vendor IDs are required when specific vendors are selected.',
                ],
                'ar' => [
                    'all_vendors_1' => 'يجب عدم تحديد البائعين عند اختيار جميع البائعين.',
                    'all_vendors_0' => 'تحديد البائعين مطلوبة عند اختيار بائعين محددين.',
                ]
            ];

            if ($request->all_vendors == 1 && !empty($request->vendor_ids)) {
                $message = $messages[$lang]['all_vendors_1'] ?? $messages['en']['all_vendors_1'];
                $validator->errors()->add('vendor_ids', $message);
            }

            if ($request->all_vendors == 0 && empty($request->vendor_ids)) {
                $message = $messages[$lang]['all_vendors_0'] ?? $messages['en']['all_vendors_0'];
                $validator->errors()->add('vendor_ids', $message);
            }
        });

        if ($validator->fails()) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        // Check if names already exist
        $existingAr = PaymentType::where('name_ar', $request->name_ar)
            ->whereNull('deleted_at')
            ->exists();
        $existingEn = PaymentType::where('name_en', $request->name_en)
            ->whereNull('deleted_at')
            ->exists();

        if ($existingAr || $existingEn) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['name' => ['Payment type name already exists']],
                'validation_type' => true
            ];
        }

        try {
            $paymentType = new PaymentType();
            $paymentType->type = $request->type;
            $paymentType->name_ar = $request->name_ar;
            $paymentType->name_en = $request->name_en;
            $paymentType->description_ar = $request->description_ar;
            $paymentType->description_en = $request->description_en;
            $paymentType->deposit = $request->deposit;
            $paymentType->payment_interval_id = $request->payment_interval_id;
            $paymentType->max_delay_percent = $request->max_delay_percent;
            $paymentType->status = $request->boolean('status');
            $paymentType->all_vendors = $request->all_vendors;

            // Set vendor_ids based on all_vendors flag
            if ($request->all_vendors == 1) {
                $paymentType->vendor_ids = null;
            } else {
                $paymentType->vendor_ids = $request->vendor_ids ?? null;
            }

            $paymentType->created_by = authActionSave()['by'];
            // $paymentType->created_by_type = authActionSave()['type'];

            $paymentType->save();

            // Load the payment interval with the payment type
            $paymentType->load('paymentInterval');

            return $paymentType;
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to create payment type',
                'data' => null,
                'errorData' => $e->getMessage(),
                'validation_type' => false
            ];
        }
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();        

        $paymentType = PaymentType::find($id);
        if (!$paymentType) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Payment type not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:Deposit Billing,Without Deposit,Advanced Payment,Deferred Payment,Periodic Payment',
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_types')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_types')->whereNull('deleted_at')->ignore($id)
            ],
            'description_ar' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'deposit' => 'required|integer|min:0|max:100',
            'payment_interval_id' => 'nullable|integer|exists:payment_intervals,id',
            'max_delay_percent' => 'nullable|integer|min:0|max:100',
            'status' => 'required|boolean',
            'all_vendors' => 'required|integer|in:0,1',
            'vendor_ids' => 'nullable|array',
            'vendor_ids.*' => 'integer|exists:vendors,id',
        ]);

        $validator->after(function ($validator) use ($request, $lang) {
            $messages = [
                'en' => [
                    'all_vendors_1' => 'Vendor IDs should not be provided when all vendors is selected.',
                    'all_vendors_0' => 'Vendor IDs are required when specific vendors are selected.',
                ],
                'ar' => [
                    'all_vendors_1' => 'يجب عدم تحديد البائعين عند اختيار جميع البائعين.',
                    'all_vendors_0' => 'تحديد البائعين مطلوبة عند اختيار بائعين محددين.',
                ]
            ];

            $selectedLang = in_array($lang, ['en', 'ar']) ? $lang : 'en';

            if ($request->all_vendors == 1 && !empty($request->vendor_ids)) {
                $validator->errors()->add('vendor_ids', $messages[$selectedLang]['all_vendors_1']);
            }

            if ($request->all_vendors == 0 && empty($request->vendor_ids)) {
                $validator->errors()->add('vendor_ids', $messages[$selectedLang]['all_vendors_0']);
            }
        });

        if ($validator->fails()) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ];
        }

        try {
            $paymentType->type = $request->type;
            $paymentType->name_ar = $request->name_ar;
            $paymentType->name_en = $request->name_en;
            $paymentType->description_ar = $request->description_ar;
            $paymentType->description_en = $request->description_en;
            $paymentType->deposit = $request->deposit;
            $paymentType->payment_interval_id = $request->payment_interval_id;
            $paymentType->max_delay_percent = $request->max_delay_percent;
            $paymentType->status = $request->boolean('status');
            $paymentType->all_vendors = $request->all_vendors;
            
            // Set vendor_ids based on all_vendors flag
            if ($request->all_vendors == 1) {
                $paymentType->vendor_ids = null;
            } else {
                $paymentType->vendor_ids = $request->vendor_ids ?? null;
            }
            
            $paymentType->modified_by = authActionSave()['by'];
            // $paymentType->modified_by_type = authActionSave()['type'];

            $paymentType->save();

            // Load the payment interval with the payment type
            $paymentType->load('paymentInterval');

            return $paymentType;
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update payment type',
                'data' => null,
                'errorData' => $e->getMessage(),
                'validation_type' => false
            ];
        }
    }

    public function delete($id)
    {
        $lang = app()->getLocale();

        $paymentType = PaymentType::find($id);
        if (!$paymentType) {
            return respondError(
                $lang == 'en'
                    ? 'Payment type not found'
                    : 'نوع الدفع غير موجود',
                404
            );
        }

        try {
            // Delete normally
            $paymentType->deleted_by = authActionSave()['by'];
            // $paymentType->deleted_by_type = authActionSave()['type'];
            $paymentType->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return respondError(
                $lang == 'en'
                    ? 'Something went wrong'
                    : 'حدث خطأ ما',
                500
            );
        }
    }
}