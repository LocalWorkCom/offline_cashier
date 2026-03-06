<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discount;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class DiscountController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');

            $discounts = Discount::with(['branches', 'dishes']); // Fetch with filtered dishes
            $discounts = paginateOrGetAll($discounts, $request);
            // Adjust for translation fields
            foreach ($discounts['data'] as $discount) {
                $discount->name = $lang === 'ar' ? $discount->name_ar : $discount->name_en;
            }

            return ResponseWithSuccessDataPaginated($lang, $discounts, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching discounts: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }



    public function show(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            // $discount = Discount::with(['branches', 'dishes'])->findOrFail($id);
            $discount = Discount::where('id', $id)->with(['branches', 'dishes'])->first();
            if(!$discount) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي الخصم.' : 'discount id not found', 404);
            }
            $discount->name = $lang === 'ar' ? $discount->name_ar : $discount->name_en;

            return ResponseWithSuccessData($lang, $discount, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching discount: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'en');
            $validator = Validator::make($request->all(), [
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric|min:0',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'required|boolean',
                'branches' => 'required|array',
                'branches.*' => 'integer|exists:branches,id',
                'dishes' => 'nullable|array',
                'dishes.*' => 'integer|exists:dishes,id',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            $validatedData = $validator->validated();

            $discount = Discount::create([
                'name_en' => $validatedData['name_en'],
                'name_ar' => $validatedData['name_ar'],
                'type' => $validatedData['type'],
                'value' => $validatedData['value'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'is_active' => $validatedData['is_active'],
                'created_by' => authActionSave()['by'],
            ]);

            if (!empty($validatedData['branches'])) {
                $discount->branches()->attach($validatedData['branches']);
            }
            if (!empty($validatedData['dishes'])) {
                $discount->dishes()->attach($validatedData['dishes']);
            }

            return ResponseWithSuccessData($lang, $discount->load('branches'), 1);
        } catch (\Exception $e) {
            Log::error('Error creating discount: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $validator = Validator::make($request->all(), [
                'name_en' => 'required|string|max:255',
                'name_ar' => 'required|string|max:255',
                'type' => 'required|in:percentage,fixed',
                'value' => 'required|numeric|min:0',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'is_active' => 'required|boolean',
                'branches' => 'required|array',
                'branches.*' => 'integer|exists:branches,id',
                'dishes' => 'nullable|array',
                'dishes.*' => 'integer|exists:dishes,id',
            ]);

            if ($validator->fails()) {
                return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
            }
            $validatedData = $validator->validated();

            // $discount = Discount::findOrFail($id);
            $discount = Discount::with('branches')->where('id', $id)->first();
            if(!$discount) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي الخصم.' : 'discount id not found', 404);
            }

            $discount->update([
                'name_en' => $validatedData['name_en'],
                'name_ar' => $validatedData['name_ar'],
                'type' => $validatedData['type'],
                'value' => $validatedData['value'],
                'start_date' => $validatedData['start_date'],
                'end_date' => $validatedData['end_date'],
                'is_active' => $validatedData['is_active'],
                'modified_by' => auth()->id(),
            ]);

            if (!empty($validatedData['branches'])) {
                $discount->branches()->sync($validatedData['branches']);
            } else {
                $discount->branches()->detach();
            }

            if (!empty($validatedData['dishes'])) {
                $discount->dishes()->sync($validatedData['dishes']);
            } else {
                $discount->dishes()->detach();
            }

            return ResponseWithSuccessData($lang, $discount, 1);
        } catch (\Exception $e) {
            Log::error('Error updating discount: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            // $discount = Discount::findOrFail($id);
            $discount = Discount::where('id', $id)->first();
            if(!$discount) {
                return respondError($lang === 'ar' ? 'لم يتم العثور علي الخصم.' : 'discount id not found', 404);
            }
            $discount->delete();

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting discount: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'en');
            $discount = Discount::withTrashed()->findOrFail($id);
            $discount->restore();

            return ResponseWithSuccessData($lang, $discount, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring discount: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
