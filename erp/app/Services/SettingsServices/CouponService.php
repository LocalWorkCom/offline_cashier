<?php


namespace App\Services\SettingsServices;

use App\Models\Branch;
use App\Models\BranchMenu;
use App\Models\BranchMenuSize;
use App\Models\Coupon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CouponService
{


    public function index($request = null)
    {
        $query = Coupon::with('branches');


        return $query;
    }

    public function show(Request $request, $id, $lang)
    {
        try {
            $coupon = Coupon::with('branches')->find($id);

            return ResponseWithSuccessData($lang, $coupon, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request, $lang)
    {

        // Define the validation rules
        $validator = Validator::make($request->all(), [
            'code' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $trimmedValue = trim($value);

                    // Check for active coupons with the same code
                    $activeExists = DB::table('coupons')
                        ->where('code', $trimmedValue)
                        ->where('is_active', 1)
                        ->whereNull('deleted_at')
                        ->exists();

                    if ($activeExists) {
                        $fail(__('coupon.duplicate_active_code'));
                    }
                },
            ],
            'type' => 'required|in:percentage,fixed',
            'apply_type' => 'nullable|required_if:type,percentage',
            'title_ar' => 'required',
            'title_en' => 'required',
            'value' => 'required|numeric|min:0',
            'minimum_spend' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_active' => 'required|boolean',
            'branches' => 'nullable|array',
            'branches.*' => 'integer|exists:branches,id',
            'branch_dishes' => 'nullable|array',
            'branch_dishes.*' => 'array',
            // 'branch_dishes.*.*' => 'integer|exists:branch_menus,id'
        ], [
            'code.required' => __('coupon.code_required'),
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->has('code')) {
                $error = $validator->errors()->first('code');
                if ($error === __('coupon.duplicate_active_code')) {
                    return CustomRespondWithBadRequest(__('coupon.duplicate_active_code'));
                }
                return CustomRespondWithBadRequest(__('coupon.code_required'));
            }
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }



        $guard = authActionSave();
        $validatedData = $validator->validated();


        // Check if coupon value exceeds minimum spend for fixed type
        if ($validatedData['type'] == 'fixed' && $validatedData['value'] > $validatedData['minimum_spend']) {
            return CustomRespondWithBadRequest(__('coupon.cannot apply coupon minimum spend not reached'));
        }

        DB::beginTransaction();
        // try {
        // Check dish prices against minimum spend if apply_type is dish
        $invalidDishes = [];
        $validDishIds = [];

        if ($request->has('apply_type') == true) {
            if ($validatedData['apply_type'] == 'dish' && !empty($validatedData['branch_dishes'])) {
                foreach ($validatedData['branch_dishes'] as $branchId => $categories) {
                    foreach ($categories as $categoryId => $dishIds) {
                        // Get dishes with their prices
                        $dishes = BranchMenu::with('dish')->whereIn('id', $dishIds)
                            ->select('id', 'dish_id', 'price')
                            ->get();

                        foreach ($dishes as $dish) {
                            if ($validatedData['minimum_spend'] && $dish->price < $validatedData['minimum_spend']) {
                                $invalidDishes[] = [
                                    'name' => $dish->name,
                                    'price' => $dish->price,
                                    'minimum_required' => $validatedData['minimum_spend']
                                ];
                            } else {
                                $validDishIds[$branchId][$categoryId][] = $dish->dish_id;
                            }
                        }
                    }
                }

                // If there are invalid dishes, return them with error message
                if (!empty($invalidDishes)) {
                    DB::rollBack();
                    $errorMessage = __('coupon.dishes_below_minimum_spend') . ': ';
                    $errorMessage .= implode(', ', array_map(function ($dish) {
                        return $dish['name'] . ' (' . $dish['price'] . ')';
                    }, $invalidDishes));

                    return respondError($errorMessage, 400, ['invalid_dishes' => $invalidDishes]);
                }
            }
        }

        // Create the coupon
        $coupon = Coupon::create([
            'code' => $validatedData['code'],
            'type' => $validatedData['type'],
            'value' => $validatedData['value'],
            'title_ar' => $validatedData['title_ar'],
            'title_en' => $validatedData['title_en'],
            'apply_type' => $validatedData['apply_type'] ?? 'order',
            'minimum_spend' => $validatedData['minimum_spend'],
            'usage_limit' => $validatedData['usage_limit'],
            'start_date' => $validatedData['start_date'],
            'end_date' => $validatedData['end_date'],
            'is_active' => $validatedData['is_active'],
            'created_by' => $guard['by'],
            'created_by_type' => $guard['type'],
            'count_usage' => 0,
        ]);

        // Attach branches and dishes
        if (!empty($validatedData['branches'])) {
            $branchCouponData = [];

            foreach ($validatedData['branches'] as $branchId) {
                $dishIdsToSave = [];

                if ($request->has('apply_type') && $validatedData['apply_type'] == 'dish' && isset($validDishIds[$branchId])) {
                    // Use the validated dish IDs (already guaranteed valid)
                    foreach ($validDishIds[$branchId] as $categoryId => $dishIds) {
                        $dishIdsToSave = array_merge($dishIdsToSave, $dishIds);
                    }
                } elseif (isset($validatedData['branch_dishes'][$branchId])) {
                    // Handle both flat & nested structures
                    foreach ($validatedData['branch_dishes'][$branchId] as $maybeCategoryId => $dishIds) {
                        // Normalize: if it's an integer, wrap in array
                        if (!is_array($dishIds)) {
                            $dishIds = [$dishIds];
                        }
                        $dishIdsToSave = array_merge($dishIdsToSave, $dishIds);
                    }
                }

                $branchCouponData[] = [
                    'coupon_id' => $coupon->id,
                    'branch_id' => $branchId,
                    'dish_ids' => json_encode(array_values(array_unique(array_map('strval', $dishIdsToSave)))),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            DB::table('branch_coupon')->insert($branchCouponData);
        }


        DB::commit();
        return ResponseWithSuccessData($lang, $coupon, 1);
        // } catch (\Exception $e) {
        //     DB::rollBack();
        //     Log::error('Error creating coupon: ' . $e->getMessage());
        //     return RespondWithBadRequestData($lang, 2);
        // }
    }

    public function update(Request $request, $id, $lang)
    {
        DB::beginTransaction();
        try {
            $coupon = Coupon::find($id);
            // dd($coupon);

            // Define the validation rules
            $validator = Validator::make($request->all(), [
                'code' => [
                    'required',
                    'string',
                    function ($attribute, $value, $fail) use ($id) {
                        $trimmedValue = trim($value);

                        // Check for active coupons with the same code (excluding current coupon)
                        $activeExists = DB::table('coupons')
                            ->where('code', $trimmedValue)
                            ->where('is_active', 1)
                            ->whereNull('deleted_at')
                            ->where('id', '!=', $id)
                            ->exists();

                        if ($activeExists) {
                            $fail(__('coupon.duplicate_active_code'));
                        }
                    },
                ],
                'type' => 'required|in:percentage,fixed',
                'title_ar' => 'nullable|string',
                'title_en' => 'nullable|string',
                'apply_type' => 'nullable|in:order,dish',
                'value' => 'required|numeric|min:0',
                'minimum_spend' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'required|boolean',
                'branches' => 'nullable|array',
                'branches.*' => 'integer|exists:branches,id',
                'branch_dishes' => 'nullable|array',
                'branch_dishes.*' => 'nullable|array',
                'branch_dishes.*.*' => 'nullable|array',
            ], [
                'code.required' => __('coupon.code_required'),
            ]);

            if ($validator->fails()) {
                if ($validator->errors()->has('code')) {
                    $error = $validator->errors()->first('code');
                    if ($error === __('coupon.duplicate_active_code')) {
                        return CustomRespondWithBadRequest(__('coupon.duplicate_active_code'));
                    }
                    return CustomRespondWithBadRequest(__('coupon.code_required'));
                }

                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $guard = authActionSave();


            // Check if there is another active coupon with the same code (excluding the current one)
            $existingActiveCoupon = Coupon::where('code', $request->code)
                ->where('is_active', true)
                ->where('id', '!=', $id) // Exclude the current coupon
                ->exists();

            if ($existingActiveCoupon) {
                return CustomRespondWithBadRequest(__('coupon.duplicate_active_code'));
            }
            $validatedData = $validator->validated();

            // Validate fixed coupon minimum spend
            if ($validatedData['type'] === 'fixed' && $validatedData['value'] > $validatedData['minimum_spend']) {
                return CustomRespondWithBadRequest(__('coupon.cannot apply coupon minimum spend not reached'));
            }

            $coupon->code = $validatedData['code'];
            $coupon->type = $validatedData['type'];
            $coupon->value = $validatedData['value'];
            $coupon->minimum_spend = $validatedData['minimum_spend'];
            if ($request->filled('apply_type')) {
                $coupon->apply_type = $validatedData['apply_type'];
            }
            if ($request->filled('title_ar')) {
                $coupon->title_ar = $validatedData['title_ar'];
            }
            if ($request->filled('title_en')) {
                $coupon->title_en  = $validatedData['title_en'];
            }
            $coupon->usage_limit = $validatedData['usage_limit'];
            $coupon->start_date = $validatedData['start_date'];
            $coupon->end_date = $validatedData['end_date'];
            $coupon->is_active = $validatedData['is_active'];
            $coupon->modified_by = $guard['by'] ?? null;
            $coupon->modified_by_type = $guard['type'] ?? null;
            $coupon->save();

            // 👇 update only if sent in request

            // Handle branches and dishes
            if (!empty($validatedData['branches'])) {
                $branchCouponData = [];

                foreach ($validatedData['branches'] as $branchId) {
                    $dishIds = [];

                    // Collect dish IDs if apply_type is dish
                    if ($validatedData['apply_type'] === 'dish' && isset($validatedData['branch_dishes'][$branchId])) {
                        foreach ($validatedData['branch_dishes'][$branchId] as $categoryDishes) {
                            $dishIds = array_merge($dishIds, $categoryDishes);
                        }
                        $dishIds = array_unique($dishIds);
                    }

                    $branchCouponData[$branchId] = [
                        'dish_ids' => json_encode($dishIds),
                        'updated_at' => now(),
                    ];
                }

                // Sync branches with dish_ids
                $coupon->branches()->sync($branchCouponData);
            } else {
                $coupon->branches()->detach();
            }

            DB::commit();
            return ResponseWithSuccessData($lang, $coupon, 1);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function destroy(Request $request, $id, $lang)
    {
        try {
            $coupon = Coupon::findOrFail($id);
            $coupon->delete();

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request, $id, $lang)
    {
        try {
            $coupon = Coupon::withTrashed()->findOrFail($id);
            $coupon->restore();

            return ResponseWithSuccessData($lang, $coupon, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring coupon: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    /**
     * Increment usage of the coupon.
     */
    public function incrementUsage($id)
    {
        try {
            $coupon = Coupon::findOrFail($id);

            // Check if coupon has a usage limit and if it's reached
            if ($coupon->usage_limit && $coupon->count_usage >= $coupon->usage_limit) {
                return response()->json([
                    'success' => false,
                    'message' => 'Coupon usage limit reached.',
                ], 400);
            }

            // Increment the count_usage
            $coupon->increment('count_usage');

            return response()->json([
                'success' => true,
                'message' => 'Coupon usage incremented successfully.',
            ]);
        } catch (\Exception $e) {
            Log::error('Error incrementing coupon usage: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error incrementing coupon usage.',
            ], 500);
        }
    }

    /**
     * Check if the coupon is still valid based on usage and date.
     */
    public function isCouponValid(Request $request, $user_id)
    {
        $code = $request->code;
        $amount = $request->amount;
        $branchId = $request->branch_id;
        $lang = $request->header('lang', 'ar');

        // try {
        // if (!CheckToken()) {
        //     return RespondWithBadRequest($lang, 4);
        // }

        // $branchId = $request->input('branch_id');

        if (!$code || empty($code)) {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Code is required.' : 'الكود مطلوب',
                'errorData' => ['error' => $lang == 'en' ? 'Code is required.' : 'الكود مطلوب'],
                'data' => null
            ]);
        }

        $coupon = GetCouponId($code, $branchId);
        if ($coupon) {
            $date = ($coupon->end_date) <= (Carbon::now());
            $startdate = ($coupon->start_date) > (Carbon::now());
            $minimum_spend = $coupon->minimum_spend;
            $usage = $coupon->count_usage >= $coupon->usage_limit;
            if ($date) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.',
                    'errorData' => ['error' => $lang == 'en' ? 'Coupon is expired.' : 'انتهت صلاحية الكوبون.'],
                    'data' => null
                ], 200);
            }

            if ($startdate) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => $lang == 'en' ? 'Coupon validity error.' : 'خطأ في صلاحية الكوبون.',
                    'errorData' => ['error' => $lang == 'en' ? 'Coupon validity error.' : 'خطأ في صلاحية الكوبون.'],
                    'data' => null
                ], 200);
            }

            if ($usage) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => $lang == 'en' ? 'Coupon reached usage limit.' : 'الكوبون وصل الحد الاقصى للاستخدام.',
                    'errorData' => ['error' => $lang == 'en' ? 'Coupon reached usage limit.' : 'الكوبون وصل الحد الاقصى للاستخدام.'],
                    'data' => null
                ], 200);
            }
            if ($amount && ($amount < $minimum_spend)) {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => $lang == 'en' ? "Can't apply coupon minimum spend not reached." : 'لا يمكن تطبيق الكوبون, لم تتجاوز الحد الادني لشراء المنتج قبل أضافه الضريبه.',
                    'errorData' => ['error' => $lang == 'en' ? "Can't apply coupon minimum spend not reached." : 'لا يمكن تطبيق الكوبون, لم تتجاوز الحد الادني للشراء.'],
                    'data' => null
                ], 200);
            }
            $valid = CheckCouponValid($coupon->id, $amount);

            if ($valid) {
                // dd($user->flag );
                if (auth()->guard('employee')->check()) {
                    $amount_after_coupon = applyCoupon($amount, $coupon);
                    $branchCountry = Branch::with('country')->find($branchId);
                    $currency_symbol = $branchCountry->country->currency_symbol;
                    $dishIds = collect($request->dishes)->pluck('dish_id')->toArray();

                    if ($coupon->apply_type == 'dish') {
                        if (!$request->has('dishes') || empty($request->dishes)) {
                            return response()->json([
                                'status' => false,
                                'code' => 400,
                                'message' => $lang == 'en' ? 'Dish IDs are required for dish coupons.' : 'يجب تحديد أطباق الكوبون.',
                                'errorData' => ['error' => $lang == 'en' ? 'Dish IDs are required for dish coupons.' : 'يجب تحديد أطباق الكوبون.'],
                                'data' => null
                            ], 200);
                        }

                        // Extract dish IDs from the new structure
                        $branch = $coupon->branches()->where('branch_id', $branchId)->first();
                        if ($branch && isset($branch->pivot->dish_ids)) {
                            $coupon_dishes = $branch->pivot->dish_ids;
                            // Convert both to arrays safely
                            $dishIdsArray = is_array($dishIds)
                                ? $dishIds
                                : (is_string($dishIds) ? json_decode($dishIds, true) : []);
                            $dishIdsArray = $dishIdsArray ?? [];

                            // For $couponDishesArray
                            $couponDishesArray = is_array($coupon_dishes)
                                ? $coupon_dishes
                                : (is_string($coupon_dishes) ? json_decode($coupon_dishes, true) : []);
                            $couponDishesArray = $couponDishesArray ?? [];

                            // Now get the actual dish records from branch_menu
                            $branchMenuDishes = BranchMenu::where('branch_id', $branch->id)
                                ->whereIn('dish_id', $couponDishesArray)
                                ->with('dish')
                                ->get();

                            // Ensure both arrays are properly formatted
                            $existingDishIds = $branchMenuDishes->pluck('id')->toArray();

                            // Ensure both arrays are properly formatted
                            $dishIdsArray = (array)$dishIdsArray;
                            $existingDishIds = (array)$existingDishIds;

                            // Find matches (dishes in both request and coupon)
                            $matchingDishes = array_intersect($dishIdsArray, $existingDishIds);
                            // dd($dishIdsArray, $existingDishIds, $matchingDishes);
                            if (!empty($matchingDishes)) {
                                // Initialize discount details collection
                                $discountDetails = collect();

                                // Process each dish in the request individually
                                foreach ($request->dishes as $requestDish) {
                                    $dishId = $requestDish['dish_id'];
                                    $sizeId = $requestDish['size_id'] ?? null;
                                    $quantity = $requestDish['quantity'];

                                    // Check if this dish is in the coupon dishes
                                    if (in_array($dishId, $matchingDishes)) {
                                        $branchMenuDish = $branchMenuDishes->firstWhere('id', $dishId);

                                        if ($branchMenuDish) {

                                            if ($sizeId) {
                                                $branchMenuSize = BranchMenuSize::where('id', $sizeId)->where('branch_id', $request->branch_id)->where('dish_id', $dishId)
                                                    ->first();
                                                if (! $branchMenuSize) {
                                                    return response()->json([
                                                        'status' => false,
                                                        'code' => 400,
                                                        'message' => $lang == 'en' ? 'Error checking coupon validity.' : 'خطأ في الكوبون',
                                                        'errorData' => ['error' => $lang == 'en' ? 'Dish size ' . $sizeId . ' of dish ' . $dishId . ' not found.' : 'حجم الطبق ' . $sizeId . ' للطبق ' . $dishId . ' غير موجود.'],
                                                        'data' => null
                                                    ], 200);
                                                }
                                                $price = $branchMenuSize ? $branchMenuSize->price : 0;
                                            } else {
                                                // Default to BranchMenu price
                                                $price = $branchMenuDish->price;
                                            }
                                            // Calculate discount per item
                                            if ($coupon->type == 'percentage') {
                                                $discountPerItem = $price * $coupon->value / 100;
                                            } else {
                                                $discountPerItem = min($coupon->value, $price);
                                            }

                                            $totalDiscount = $discountPerItem * $quantity;

                                            // Add each item individually to the discount details
                                            for ($i = 0; $i < $quantity; $i++) {
                                                $discountDetails->push([
                                                    'dish_id' => $dishId,
                                                    'size_id' => $sizeId,
                                                    'original_price' => $price,
                                                    'totalDiscount' => $discountPerItem,
                                                    'dish_price_after_discount' => $price - $discountPerItem,
                                                ]);
                                            }
                                        }
                                    }
                                }

                                // Calculate totals
                                $totalDiscount = $discountDetails->sum('totalDiscount');
                                $finalAmount = max(0, $amount - $totalDiscount); // Ensure amount doesn't go negative

                                $discountDetailsWithoutTotal = $discountDetails->map(function ($item) {
                                    return collect($item)->only(['dish_id', 'size_id', 'original_price', 'dish_price_after_discount']);
                                });

                                $branchCountry = Branch::with('country')->find($branchId);
                                $currency_symbol = $branchCountry->country->currency_symbol;

                                return response()->json([
                                    'status' => true,
                                    'code' => 200,
                                    'message' => $lang == 'en' ? 'Coupon is valid.' : 'الكوبون صالح للاستخدام.',
                                    'data' => [
                                        'coupon_title' => $coupon->title,
                                        'coupon_value' => $coupon->value,
                                        'value_type' => $coupon->type,
                                        'coupon_apply_type' => $coupon->apply_type,
                                        'amount_after_coupon' => $finalAmount,
                                        'total_discount' => $totalDiscount,
                                        'currency_symbol' => $currency_symbol,
                                        'discount_details' => $discountDetailsWithoutTotal
                                    ],
                                ], 200);
                            } else {
                                return response()->json([
                                    'status' => false,
                                    'code' => 400,
                                    'message' => $lang == 'en' ? 'Error checking coupon validity.' : 'خطأ في الكوبون',
                                    'errorData' => ['error' => $lang == 'en' ? 'there is no dishes of coupon matches with your order' : 'لا توجد أطباق مطابقة للكوبون في طلبك'],
                                    'data' => null
                                ], 200);
                            }
                        } else {
                            return response()->json([
                                'status' => false,
                                'code' => 400,
                                'message' => $lang == 'en' ? 'Coupon dishes not found.' : 'لم يتم العثور على أطباق الكوبون.',
                                'errorData' => ['error' => $lang == 'en' ? 'this coupon does not have dishes.' : 'هذا الكوبون لا يحتوي على أطباق.'],
                                'data' => null
                            ], 200);
                        }
                    } elseif ($coupon->apply_type == 'order') {
                        $amount_after_coupon = applyCoupon($amount, $coupon);
                        $branchCountry = Branch::with('country')->find($branchId);
                        $currency_symbol = $branchCountry->country->currency_symbol;
                        $responseData = [
                            'coupon_title' => $coupon->title,
                            'coupon_value' => $coupon->value,
                            'value_type' => $coupon->type,
                            'coupon_apply_type' => $coupon->apply_type,
                            'amount_after_coupon' => $amount_after_coupon,
                            'total_discount' => $amount - $amount_after_coupon,
                            'currency_symbol' => $currency_symbol,
                        ];

                        // Add delivery_fees conditionally
                        if ($coupon->type == 'percentage' && $coupon->value == 100 && $request->order_type == 'delivery') {
                            $responseData['delivery_fees'] = 0;
                        }

                        return response()->json([
                            'status'  => true,
                            'code'    => 200,
                            'message' => $lang == 'en' ? 'Coupon is valid.' : 'الكوبون صالح للاستخدام.',
                            'data'    => $responseData,
                        ], 200);
                    } else {
                        $user = User::where('id', $user_id)->first();
                        $amount_after_coupon = applyCoupon($amount, $coupon);
                        $branchCountry = Branch::with('country')->find($branchId);
                        $currency_symbol = $branchCountry->country->currency_symbol;
                        $responseData = [
                            'coupon_title' => $coupon->title,
                            'coupon_value' => $coupon->value,
                            'value_type' => $coupon->type,
                            'coupon_apply_type' => $coupon->apply_type,
                            'amount_after_coupon' => $amount_after_coupon,
                            'total_discount' => $amount - $amount_after_coupon,
                            'currency_symbol' => $currency_symbol
                        ];

                        // Add delivery_fees conditionally
                        if ($coupon->type == 'percentage' && $coupon->value == 100 && $request->order_type == 'delivery') {
                            $responseData['delivery_fees'] = 0;
                        }


                        if ($user->flag != 'unknown') {
                            $check = CheckUserCouponUsage($coupon->id, $user_id);

                            if ($check && !(auth()->guard('employee')->check())) {

                                return response()->json([
                                    'status' => false,
                                    'code' => 400,
                                    'message' => $lang == 'en' ? 'Coupon is used before.' : 'تم استخدام الكوبون.',
                                    'errorData' => ['error' => $lang == 'en' ? 'Coupon is used before.' : 'تم استخدام الكوبون.'],
                                    'data' => null
                                ], 200);
                            }

                            return response()->json([
                                'status'  => true,
                                'code'    => 200,
                                'message' => $lang == 'en' ? 'Coupon is valid.' : 'الكوبون صالح للاستخدام.',
                                'data'    => $responseData,
                            ], 200);
                        } else {

                            return response()->json([
                                'status'  => true,
                                'code'    => 200,
                                'message' => $lang == 'en' ? 'Coupon is valid.' : 'الكوبون صالح للاستخدام.',
                                'data'    => $responseData,
                            ], 200);
                        }
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'code' => 400,
                        'message' => $lang == 'en' ? 'Coupon is invalid.' : 'الكوبون غير صالح للاستخدام.',
                        'errorData' => ['error' => $lang == 'en' ? 'Coupon is invalid.' : 'الكوبون غير صالح للاستخدام.'],
                        'data' => null
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'code' => 400,
                    'message' => $lang == 'en' ? 'Coupon is invalid.' : 'الكوبون غير صالح للاستخدام.',
                    'errorData' => ['error' => $lang == 'en' ? 'Coupon is invalid.' : 'الكوبون غير صالح للاستخدام.'],
                    'data' => null
                ], 200);
            }
            // } catch (\Exception $e) {
            //     return response()->json([
            //         'status' => false,
            //         'code' => 400,
            //         'message' => $lang == 'en' ? 'Error checking coupon validity.' : 'خطأ في الكوبون',
            //         'errorData' => ['error' => $lang == 'en' ? 'Error checking coupon validity.' : 'خطأ في الكوبون'],
            //         'data' => null
            //     ], 200);
            // }
        } else {
            return response()->json([
                'status' => false,
                'code' => 400,
                'message' => $lang == 'en' ? 'Coupon not found.' : 'لم يتم العثور على  الكوبون.',
                'errorData' => ['error' => $lang == 'en' ? 'this coupon does not exist.' : 'هذا الكوبون غير موجود.'],
                'data' => null
            ], 200);
        }
    }
}
