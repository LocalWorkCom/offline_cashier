<?php

namespace App\Services\ProcurementServices;

use App\Models\PaymentMethod;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentMethodService
{
    public function index(Request $request)
    {
        $lang = app()->getLocale();

        // Return query builder with vendors count
        $query = PaymentMethod::query()
            ->addSelect([
                'vendors_count' => DB::table('vendors')
                    ->selectRaw('COUNT(*)')
                    ->where(function ($query) {
                        $query->whereRaw('payment_methods.all_vendors = 1')
                            ->orWhereRaw('JSON_CONTAINS(payment_methods.vendor_ids, JSON_ARRAY(vendors.id))');
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
            'type' => 'required|string|in:cash,visa',
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods')->whereNull('deleted_at')
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods')->whereNull('deleted_at')
            ],
            'description_ar' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'additional_info' => 'nullable|string|max:1000',
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
        $existingAr = PaymentMethod::where('name_ar', $request->name_ar)
            ->whereNull('deleted_at')
            ->exists();
        $existingEn = PaymentMethod::where('name_en', $request->name_en)
            ->whereNull('deleted_at')
            ->exists();

        if ($existingAr || $existingEn) {
            return [
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['name' => ['Payment method name already exists']],
                'validation_type' => true
            ];
        }

        try {
            $paymentMethod = new PaymentMethod();
            $paymentMethod->type = $request->type;
            $paymentMethod->name_ar = $request->name_ar;
            $paymentMethod->name_en = $request->name_en;
            $paymentMethod->description_ar = $request->description_ar;
            $paymentMethod->description_en = $request->description_en;
            $paymentMethod->additional_info = $request->additional_info;
            $paymentMethod->status = $request->boolean('status');
            $paymentMethod->all_vendors = $request->all_vendors;

            // Set vendor_ids based on all_vendors flag
            if ($request->all_vendors == 1) {
                $paymentMethod->vendor_ids = null; // Empty array when all vendors
            } else {
                $paymentMethod->vendor_ids = $request->vendor_ids ?? null;
            }

            $paymentMethod->created_by = authActionSave()['by'];
            $paymentMethod->created_by_type = authActionSave()['type'];

            $paymentMethod->save();

            return $paymentMethod;
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to create payment method',
                'data' => null,
                'errorData' => $e->getMessage(),
                'validation_type' => false
            ];
        }
    }

    public function update(Request $request, $id)
    {
        $lang = app()->getLocale();        

        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return [
                'code' => 404,
                'status' => false,
                'message' => 'Payment method not found',
                'data' => null,
                'errorData' => null,
                'validation_type' => false
            ];
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string|in:cash,visa',
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods')->whereNull('deleted_at')->ignore($id)
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('payment_methods')->whereNull('deleted_at')->ignore($id)
            ],
            'description_ar' => 'nullable|string|max:500',
            'description_en' => 'nullable|string|max:500',
            'additional_info' => 'nullable|string|max:1000',
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
            $paymentMethod->type = $request->type;
            $paymentMethod->name_ar = $request->name_ar;
            $paymentMethod->name_en = $request->name_en;
            $paymentMethod->description_ar = $request->description_ar;
            $paymentMethod->description_en = $request->description_en;
            $paymentMethod->additional_info = $request->additional_info;
            $paymentMethod->status = $request->boolean('status');
            $paymentMethod->all_vendors = $request->all_vendors;
            
            // Set vendor_ids based on all_vendors flag
            if ($request->all_vendors == 1) {
                $paymentMethod->vendor_ids = null;
            } else {
                $paymentMethod->vendor_ids = $request->vendor_ids ?? null;
            }
            
            $paymentMethod->modified_by = authActionSave()['by'];
            $paymentMethod->modified_by_type = authActionSave()['type'];

            $paymentMethod->save();

            return $paymentMethod;
        } catch (\Exception $e) {
            return [
                'code' => 500,
                'status' => false,
                'message' => 'Failed to update payment method',
                'data' => null,
                'errorData' => $e->getMessage(),
                'validation_type' => false
            ];
        }
    }

    public function delete($id)
    {
        $lang = app()->getLocale();

        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return respondError(
                $lang == 'en'
                    ? 'Payment method not found'
                    : 'طريقة الدفع غير موجودة',
                404
            );
        }

        try {
            // if ($paymentMethod->vendors()->exists()) {
            //     // Deactivate instead of delete
            //     $paymentMethod->update([
            //         'status' => 0,
            //         'modified_by' => authActionSave()['by'],
            //         'modified_by_type' => authActionSave()['type']
            //     ]);

            //     return respondError(
            //         $lang == 'en'
            //             ? 'This payment method is now inactive. Existing vendors remain linked, but it cannot be used for new assignments.'
            //             : 'هذه الطريقة غير نشطة الآن. الموردون الحاليون يظلون مرتبطين.',
            //         400
            //     );
            // }

            // Delete normally
            $paymentMethod->deleted_by = authActionSave()['by'];
            $paymentMethod->deleted_by_type = authActionSave()['type'];
            $paymentMethod->delete();

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