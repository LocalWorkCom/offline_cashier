<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Resources\purchase\VendorBasicResource;
use App\Http\Resources\purchase\VendorResource;
use App\Models\Country;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use App\Models\Vendor;
use App\Services\ProcurementServices\VendorService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class VendorController extends Controller
{
    protected $vendorService;

    public function __construct(VendorService $vendorService)
    {
        $this->vendorService = $vendorService;
    }

    private function getAuthenticatedEmployee()
    {
        $employee = auth('employee')->user();
        if (!$employee) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
        return $employee;
    }

    public function index(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            // Use service to get the query builder
            $query = $this->vendorService->index($request);

            //  Pagination or all results
            $vendors = paginateOrGetAll($query, $request, null, null);
            $result = VendorResource::collection($vendors['data'])->resolve();

            return ResponseWithSuccessDataPaginated($lang,  ['data' => $result, 'meta' => $vendors['meta']], 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve payment intervals',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

            $vendorExists = Vendor::where('id', $id)->exists();

            if (!$vendorExists) {
                return respondError($lang === 'ar' ? 'المورد غير موجود' : 'vendor not found', 404);
            }

            // Check if 'basic' filter/input is sent
            $isBasic = $request->input('basic', false);

            // Get the vendor data from the service
            $vendorData = $this->vendorService->show($request, $id);

            // Use appropriate resource
            if ($isBasic) {
                $responseData = new VendorBasicResource($vendorData, $lang);
            } else {
                $responseData = new VendorResource($vendorData, $lang);
            }

            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve vendor',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function store(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');
            $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'min:1',
                    Rule::unique('vendors', 'name_ar')->where(
                        fn($query) =>
                        $query->where('type', $request->type)
                    ),
                ],

                'name_en' => [
                    'required',
                    'string',
                    'min:1',
                    Rule::unique('vendors', 'name_en')->where(
                        fn($query) =>
                        $query->where('type', $request->type)
                    ),
                ],

                'type' => 'required|string|in:individual,company,local_market',

                'country_code' => 'required|string|exists:countries,phone_code',

                'phone' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $country = Country::where('phone_code', $request->country_code)->first();
                        if ($country && isset($country->phone_length)) {
                            if (strlen($value) != $country->phone_length) {
                                $fail("Phone number must be exactly {$country->phone_length} digits for this country.");
                            }
                        }
                    }
                ],
                'email' => 'required|email',
                'address' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string',
                'communication_method' => 'required|array',
//                'remaining_credit' => 'required|integer',
//                'credit_balance' => 'required|integer',
                'payment_methods' => 'required|array',
                'payment_methods.*' => 'required|integer',

                'payment_types' => 'required|array',
                'payment_types.*' => 'required|integer',
                'is_active' => 'required|in:0,1',
                'categories' => ['required', 'array', function($attribute, $value, $fail) {
                    $validCategories = Category::whereIn('id', $value)
                        ->where('active', 1)
                        ->whereNull('deleted_at')
                        ->pluck('id')
                        ->toArray();

                    if (count($validCategories) !== count($value)) {
                        $fail(__('validation.invalid_category'));
                    }
                }],

                'sub_categories' => ['required', 'array', function($attribute, $value, $fail) use ($request) {
                    // Check subcategories exist, active, not deleted
                    $validSubCategories = Category::whereIn('id', $value)
                        ->where('active', 1)
                        ->whereNotNull('parent_id') // subcategories must have a parent
                        ->whereNull('deleted_at')
                        ->pluck('id', 'parent_id'); // key = parent_id, value = subcategory id

                    // Check each sub_category is a child of one of the selected categories
                    foreach ($value as $subId) {
                        $sub = Category::where('id', $subId)->first();
                        if (!$sub || !in_array($sub->parent_id, $request->categories)) {
                            $fail(__('validation.invalid_subcategory', ['id' => $subId]));
                        }
                    }
                }],
                'tax_card_number' => [
                    'required_if:type,company',
                    'string'
                ],

                'commercial_registration_number' => [
                    'required_if:type,company',
                    'string'
                ],
                'contact_name' => 'nullable|string',
                'contact_country_code' => 'nullable|string|exists:countries,phone_code',

                'contact_phone' => [
                    'nullable',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $country = Country::where('phone_code', $request->country_code)->first();
                        if ($country && isset($country->phone_length)) {
                            if (strlen($value) != $country->phone_length) {
                                $fail("Phone number must be exactly {$country->phone_length} digits for this country.");
                            }
                        }
                    }
                ],
                'contact_email' => 'nullable|email',
            ]);
            $validPaymentMethods = PaymentMethod::whereIn('id', $request->payment_methods)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (count($validPaymentMethods) !== count($request->payment_methods)) {
                return respondError(
                    $lang === 'ar'
                        ? 'طرق الدفع غير صالحة أو غير مفعلة'
                        : 'Invalid or inactive payment methods',
                    422
                );
            }

            $validPaymentTypes = PaymentType::whereIn('id', $request->payment_types)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (count($validPaymentTypes) !== count($request->payment_types)) {
                return respondError(
                    $lang === 'ar'
                        ? 'أنواع الدفع غير صالحة أو غير مفعلة'
                        : 'Invalid or inactive payment types',
                    422
                );
            }
            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
            }

            $result = $this->vendorService->store($request);

            $responseData = new VendorResource($result, $lang);


            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating payment interval',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {

//        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'en');

        $vendor = Vendor::with(['info', 'categories', 'paymentMethods', 'paymentTypes'])->find($id);

        if (!$vendor) {
            return respondError(__('validation.not_found'), 404);
        }


        $validator = Validator::make($request->all(), [
                'name_ar' => [
                    'required',
                    'string',
                    'min:1',
                    Rule::unique('vendors', 'name_ar')->ignore($vendor->id)->where(fn($query) => $query->where('type', $request->type)),
                ],
                'name_en' => [
                    'required',
                    'string',
                    'min:1',
                    Rule::unique('vendors', 'name_en')->ignore($vendor->id)->where(fn($query) => $query->where('type', $request->type)),
                ],
                'type' => 'required|string|in:individual,company,local_market',
                'country_code' => 'required|string|exists:countries,phone_code',
                'phone' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $country = Country::where('phone_code', $request->country_code)->first();
                        if ($country && isset($country->phone_length)) {
                            if (strlen($value) != $country->phone_length) {
                                $fail("Phone number must be exactly {$country->phone_length} digits for this country.");
                            }
                        }
                    }
                ],
                'email' => 'required|email',
                'address' => 'required|string',
                'latitude' => 'required|string',
                'longitude' => 'required|string',
                'communication_method' => 'required|array',
//                'remaining_credit' => 'required|integer',
//                'credit_balance' => 'required|integer',
                'payment_methods' => 'required|array',
                'payment_methods.*' => 'required|integer',
                'payment_types' => 'required|array',
                'payment_types.*' => 'required|integer',
                'is_active' => 'required|in:0,1',
            'categories' => ['required', 'array', function($attribute, $value, $fail) {
                $validCategories = Category::whereIn('id', $value)
                    ->where('active', 1)
                    ->whereNull('deleted_at')
                    ->pluck('id')
                    ->toArray();

                if (count($validCategories) !== count($value)) {
                    $fail(__('validation.invalid_category'));
                }
            }],

            'sub_categories' => ['required', 'array', function($attribute, $value, $fail) use ($request) {
                // Check subcategories exist, active, not deleted
                $validSubCategories = Category::whereIn('id', $value)
                    ->where('active', 1)
                    ->whereNotNull('parent_id') // subcategories must have a parent
                    ->whereNull('deleted_at')
                    ->pluck('id', 'parent_id'); // key = parent_id, value = subcategory id

                // Check each sub_category is a child of one of the selected categories
                foreach ($value as $subId) {
                    $sub = Category::where('id', $subId)->first();
                    if (!$sub || !in_array($sub->parent_id, $request->categories)) {
                        $fail(__('validation.invalid_subcategory', ['id' => $subId]));
                    }
                }
            }],
                'tax_card_number' => 'required|string',
                'commercial_registration_number' => 'required|string',
                'contact_name' => 'required|string',
                'contact_country_code' => 'required|string|exists:countries,phone_code',
                'contact_phone' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($request) {
                        $country = Country::where('phone_code', $request->contact_country_code)->first();
                        if ($country && isset($country->phone_length)) {
                            if (strlen($value) != $country->phone_length) {
                                $fail("Contact phone must be exactly {$country->phone_length} digits for this country.");
                            }
                        }
                    }
                ],
                'contact_email' => 'required|email',
            ]);

            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
            }

            // Validate payment methods
            $validPaymentMethods = PaymentMethod::whereIn('id', $request->payment_methods)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (count($validPaymentMethods) !== count($request->payment_methods)) {
                return respondError($lang === 'ar' ? 'طرق الدفع غير صالحة أو غير مفعلة' : 'Invalid or inactive payment methods', 422);
            }

            // Validate payment types
            $validPaymentTypes = PaymentType::whereIn('id', $request->payment_types)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->pluck('id')
                ->toArray();

            if (count($validPaymentTypes) !== count($request->payment_types)) {
                return respondError($lang === 'ar' ? 'أنواع الدفع غير صالحة أو غير مفعلة' : 'Invalid or inactive payment types', 422);
            }
            $VendorResource = $this->vendorService->update($request, $vendor);

            $responseData = new VendorResource($VendorResource, $lang);

            return ResponseWithSuccessData($lang, $responseData, 1);
//        } catch (\Exception $e) {
//            DB::rollBack();
//            return response()->json([
//                'message' => $lang === 'ar' ? 'حدث خطأ أثناء تحديث المورد' : 'An error occurred while updating vendor',
//                'error' => $e->getMessage()
//            ], 500);
//        }
    }

    public function destroy(Request $request, $id = null)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        // Determine IDs: either from route or from request
        $ids = $id ? [$id] : $request->input('ids', []);

        // Validate IDs
        $validator = Validator::make(['ids' => $ids], [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:vendors,id',
        ]);

        if ($validator->fails()) {
            return respondError(
                $lang === 'ar' ? 'خطأ في التحقق' : 'Validation error',
                400,
                $validator->errors()
            );
        }

        DB::beginTransaction();

        try {
            Vendor::whereIn('id', $ids)->update(['deleted_by' => authActionSave()['by']]);
            Vendor::whereIn('id', $ids)->delete();

            DB::commit();

            return ResponseWithSuccessData($lang, ['deleted_ids' => $ids], 1);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'Failed to delete vendors',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
