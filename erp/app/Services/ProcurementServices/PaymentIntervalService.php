<?php

namespace App\Services\ProcurementServices;

use App\Models\PaymentInterval;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentIntervalService
{
    public function index(Request $request)
    {
        $lang = app()->getLocale();

        // Return query builder with vendors count
        $query = PaymentInterval::query()
            ->addSelect([
                'vendors_count' => DB::table('vendors')
                    ->selectRaw('COUNT(*)')
                    ->where(function ($query) {
                        $query->whereRaw('payment_intervals.all_vendors = 1')
                            ->orWhereRaw('JSON_CONTAINS(payment_intervals.vendor_ids, JSON_ARRAY(vendors.id))');
                    })
                    ->whereNull('vendors.deleted_at') // Only count non-deleted vendors
            ])
            ->with(['createdBy', 'modifiedBy'])
            ->orderByDesc('updated_at');

        return $query;
    }

    public function store(Request $request)
    {
        $lang = app()->getLocale();

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_intervals')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_intervals')->whereNull('deleted_at')
            ],
            'number_of_days' => 'required|integer|min:1',
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
        $existingAr = PaymentInterval::where('name_ar', $request->name_ar)
            ->whereNull('deleted_at')
            ->exists();
        $existingEn = PaymentInterval::where('name_en', $request->name_en)
            ->whereNull('deleted_at')
            ->exists();

        if ($existingAr || $existingEn) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['name' => ['Payment interval name already exists']],
                'validation_type' => true
            ];
        }

        try {
            $paymentInterval = new PaymentInterval();
            $paymentInterval->name_ar = $request->name_ar;
            $paymentInterval->name_en = $request->name_en;
            $paymentInterval->number_of_days = $request->number_of_days;
            $paymentInterval->status = $request->boolean('status');
            $paymentInterval->all_vendors = $request->all_vendors;

            // Set vendor_ids based on all_vendors flag
            if ($request->all_vendors == 1) {
                $paymentInterval->vendor_ids = null; // Empty array when all vendors
            } else {
                $paymentInterval->vendor_ids = $request->vendor_ids ?? null;
            }

            $paymentInterval->created_by = authActionSave()['by'];
            $paymentInterval->created_by_type = authActionSave()['type'];

            $paymentInterval->save();

            return $paymentInterval;
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to create payment interval',
                'data' => null,
                'errorData' => $e->getMessage(),
                'validation_type' => false
            ];
        }
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();

        $paymentInterval = PaymentInterval::find($id);
        if (!$paymentInterval) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Payment interval not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_intervals')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_intervals')->whereNull('deleted_at')->ignore($id)
            ],
            'number_of_days' => 'required|integer|min:1',
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
            $paymentInterval->name_ar = $request->name_ar;
            $paymentInterval->name_en = $request->name_en;
            $paymentInterval->number_of_days = $request->number_of_days;
            $paymentInterval->status = $request->boolean('status');
            $paymentInterval->all_vendors = $request->all_vendors;
            $paymentInterval->vendor_ids = $request->vendor_ids ?? null;
            $paymentInterval->modified_by = authActionSave()['by'];
            $paymentInterval->modified_by_type = authActionSave()['type'];

            $paymentInterval->save();

            return $paymentInterval;
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update payment interval',
                'data' => null,
                'errorData' => $e->getMessage(),
                'validation_type' => false
            ];
        }
    }

    public function delete($id)
    {
        $lang = app()->getLocale();

        $paymentInterval = PaymentInterval::find($id);
        if (!$paymentInterval) {
            return respondError(
                $lang == 'en'
                    ? 'Payment interval not found'
                    : 'فترة الدفع غير موجودة',
                404
            );
        }

        try {

            // if ($paymentInterval->vendors()->exists()) {

            //     // Deactivate instead of delete
            //     $paymentInterval->update([
            //         'status' => 0,
            //         'modified_by' => authActionSave()['by'],
            //         'modified_by_type' => authActionSave()['type']
            //     ]);

            //     return respondError(
            //         $lang == 'en'
            //             ? 'This payment interval is now inactive. Existing vendors remain linked, but it cannot be used for new assignments.'
            //             : 'هذه الفترة غير نشطة الآن. الموردون الحاليون يظلون مرتبطين.',
            //         400
            //     );
            // }

            // Delete normally
            $paymentInterval->deleted_by = authActionSave()['by'];
            $paymentInterval->deleted_by_type = authActionSave()['type'];
            $paymentInterval->delete();

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
