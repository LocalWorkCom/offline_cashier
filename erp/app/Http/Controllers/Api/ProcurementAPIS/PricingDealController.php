<?php

namespace App\Http\Controllers\Api\ProcurementAPIS;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use App\Http\Controllers\Controller;
use App\Http\Resources\purchase\PricingDealResource;
use App\Models\Country;
use App\Models\PaymentMethod;
use App\Models\PaymentType;
use App\Models\PricingDeal as ModelsPricingDeal;
use App\Models\Vendor;
use App\Services\ProcurementServices\PricingDealService;
use App\Services\ProcurementServices\PricingDeal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class PricingDealController extends Controller
{
    protected $PricingDeal;

    public function __construct(PricingDealService $PricingDeal)
    {
        $this->PricingDeal = $PricingDeal;
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
            $query = $this->PricingDeal->index($request);

            //  Pagination or all results
            $vendors = paginateOrGetAll($query, $request, null, null);
            $result = PricingDealResource::collection($vendors['data'])->resolve();

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

            $PricingDealExists = ModelsPricingDeal::where('id', $id)->exists();

            if (!$PricingDealExists) {
                return respondError($lang === 'ar' ? 'عرض السعر غير موجود' : 'pricing deal not found', 404);
            }
            $isBasic = $request->input('basic', false);

            // Use service to get the base query
            $PricingDealResource = $this->PricingDeal->show($request, $id);

            $responseData = new PricingDealResource($PricingDealResource, $lang);
            if ($isBasic) {
                $responseData = $responseData->resolve($request); // convert to array
            }
            return ResponseWithSuccessData($lang, $responseData, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve ',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'ar');

            // Validation
            $validator = Validator::make($request->all(), [
                'vendor_id' => [
                    'required',
                    Rule::exists('vendors', 'id')->where(function ($query) {
                        $query->where('is_active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'deal_type' => 'required|in:one_time,contract',
                'file' => 'required|file|mimes:pdf',
                'period' => 'nullable|integer|min:0',
                'total_after_negotiated' => 'nullable|integer|min:0',
                'start' => 'required|date|date_format:Y-m-d|after_or_equal:today',
                'end' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
                'items' => 'required|array|min:1',
                'items.*.product_id' => [
                    'nullable',
                    Rule::exists('products', 'id')->where(function ($query) {
                        $query->whereNull('deleted_at');
                    }),
                ],
                'items.*.brand_id' => [
                    'nullable',
                    Rule::exists('brands', 'id')->where(function ($query) {
                        $query->where('is_active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'items.*.category_id' => [
                    'nullable',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'items.*.unit_id' => [
                    'nullable',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.unit_price' => 'nullable|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
                'items.*.tax' => 'nullable|numeric|min:0',
                'items.*.net_amount' => 'nullable|numeric|min:0',
                'items.*.notes' => ['nullable', 'string', 'regex:/\S+/'],
                'shipment' => 'nullable|array',
                'shipment.vendor_id' => [
                    'nullable',
                    Rule::exists('vendors', 'id')->where(function ($query) {
                        $query->where('is_active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'shipment.type' => 'nullable|in:self,vendor,external',

                'shipment.from_address' => [
                    'required_unless:shipment.type,self',
                    'string',
                    'regex:/\S+/'
                ],

                'shipment.to_address' => [
                    'required_unless:shipment.type,self',
                    'string',
                    'regex:/\S+/'
                ],

                'shipment.price' => [
                    'required_unless:shipment.type,self',
                    'numeric',
                    'min:0'
                ],

                'shipment.pricing_basis' => [
                    'required_unless:shipment.type,self',
                    'in:per order, per shipment, or per unit basis, free'

                ],

                'shipment.notes' => ['nullable', 'string', 'regex:/\S+/'],
                'shipment.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            ]);

            $validator->after(function ($validator) use ($request) {
                $categoryIds = collect($request->items)->pluck('category_id')->filter();
                if ($categoryIds->unique()->count() > 1) {
                    $validator->errors()->add('items', 'All products must belong to the same category.');
                }
            });
            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
            }
            $deal = $this->PricingDeal->store($request);
            return ResponseWithSuccessData($lang, $deal, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while creating payment interval',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $this->getAuthenticatedEmployee();
            $lang = $request->header('lang', 'ar');

            // Validation same as store
            $validator = Validator::make($request->all(), [
                'vendor_id' => [
                    'required',
                    Rule::exists('vendors', 'id')->where(function ($query) {
                        $query->where('is_active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'deal_type' => 'required|in:one_time,contract',
                'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
                'period' => 'nullable|integer',
                'start' => 'required|date|date_format:Y-m-d|after_or_equal:today',
                'end' => 'nullable|date|date_format:Y-m-d|after_or_equal:today',
                'items' => 'required|array|min:1',
                'items.*.id' => 'nullable|exists:pricing_deal_items,id',
                'items.*.product_id' => [
                    'nullable',
                    Rule::exists('products', 'id')->where(function ($query) {
                        $query->whereNull('deleted_at');
                    }),
                ],

                'items.*.brand_id' => [
                    'nullable',
                    Rule::exists('brands', 'id')->where(function ($query) {
                        $query->where('is_active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],

                'items.*.category_id' => [
                    'nullable',
                    Rule::exists('categories', 'id')->where(function ($query) {
                        $query->where('active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],

                'items.*.unit_id' => [
                    'nullable',
                    Rule::exists('units', 'id')->where(function ($query) {
                        $query->where('active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'items.*.quantity' => 'nullable|integer|min:1',
                'items.*.unit_price' => 'nullable|numeric|min:0',
                'items.*.discount' => 'nullable|numeric|min:0',
                'items.*.tax' => 'nullable|numeric|min:0',
                'items.*.net_amount' => 'nullable|numeric|min:0',
                'items.*.notes' => ['nullable', 'string', 'regex:/\S+/'],
                'shipment' => 'nullable|array',
                'shipment.type' => 'nullable|in:self,vendor,external',
                'shipment.vendor_id' => [
                    'nullable',
                    Rule::exists('vendors', 'id')->where(function ($query) {
                        $query->where('is_active', 1)
                            ->whereNull('deleted_at');
                    }),
                ],
                'shipment.from_address' => [
                    'required_with:shipment.type',
                    'string',
                    'regex:/\S+/'
                ],
                'shipment.to_address' => [
                    'required_with:shipment.type',
                    'string',
                    'regex:/\S+/'
                ],
                'shipment.price' => [
                    'required_with:shipment.type',
                    'numeric',
                    'min:0'
                ],
                'shipment.pricing_basis' => [
                    'required_with:shipment.type',
                    'in:per_order,per_kg,per_package,per_distance'
                ],
                'shipment.notes' => ['nullable', 'string', 'regex:/\S+/'],
                'shipment.file' => 'nullable|file|mimes:pdf,jpg,jpeg,png',
            ]);
            $validator->after(function ($validator) use ($request) {
                $categoryIds = collect($request->items)->pluck('category_id')->filter();
                if ($categoryIds->unique()->count() > 1) {
                    $validator->errors()->add('items', 'All products must belong to the same category.');
                }
            });
            if ($validator->fails()) {
                return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
            }

            // Pass to service
            $deal = $this->PricingDeal->update($request, $id);

            return ResponseWithSuccessData($lang, $deal, 1);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $lang === 'ar' ? 'حدث خطأ أثناء تحديث المورد' : 'An error occurred while updating the deal',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function destroy(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        App::setLocale($lang);

        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:vendors,id',
        ]);
        if ($validator->fails()) {
            return respondError($lang === 'ar' ? 'خطأ في التحقق' : 'Validation error', 400, $validator->errors());
        }
        $ids = $request->ids;

        DB::beginTransaction();

        try {
            ModelsPricingDeal::whereIn('id', $ids)->update(['deleted_by' => authActionSave()['by']]);
            ModelsPricingDeal::whereIn('id', $ids)->delete();

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
