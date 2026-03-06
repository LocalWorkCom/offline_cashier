<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Models\Slider;
use App\Traits\SliderTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\OfferResource;
use App\Services\SettingsServices\SliderService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SliderController extends Controller
{
    use SliderTrait;

    protected $SliderService;

    public function __construct(SliderService $SliderService)
    {
        $this->SliderService = $SliderService;
    }
    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        try {
            // Get the query builder from service
            $query = $this->SliderService->index($request);

            // Handle pagination or get all
            $response = paginateOrGetAll($query, $request, null);

            $response['data'] = $response['data']->map(function ($item) {
                if ($item->offer) {
                    $item->offer->discount_type_lang = __('offer.' . strtolower($item->offer->discount_type));
                }
                return $item;
            });

            return ResponseWithSuccessDataPaginated($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching Slider : ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        app()->setLocale($lang);

        $validator = Validator::make($request->all(), [
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'nullable|string|max:255',
            'description_en' => 'nullable|string|max:255',
            'flag' => 'required|in:discount,offer,dish',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'dish_id' => 'nullable',
            'offer_id' => 'nullable',
            'discount_id' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => $validator->errors(),
                'validation_type' => true
            ], 400);
        }

        if (empty($request->offer_id) && empty($request->discount_id) && empty($request->dish_id)) {
            return response()->json([
                'code' => 400,
                'status' => false,
                'message' => 'Validation Error.',
                'data' => null,
                'errorData' => ['error' => [$lang == 'en' ? 'At least one of offer_id, discount_id, or dish_id must be send.' : 'يجب ارسال واحد على الأقل من offer_id أو discount_id أو dish_id.']],
                'validation_type' => true
            ], 400);
        }

        if ($request->flag == 'offer') {
            if (!empty($request->dish_id) || !empty($request->discount_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['error' => [$lang == 'en' ? 'When flag is offer, only offer_id should be sent.' : 'عندما تكون النوع عرض، يجب ارسال معرف العرض فقط.']],
                    'validation_type' => true
                ], 400);
            }
            if (empty($request->offer_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['error' => [$lang == 'en' ? 'offer_id is required when flag is offer.' : 'معرف العرض فقط مطلوب عندما تكون النوع عرض.']],
                    'validation_type' => true
                ], 400);
            }
        }
        if ($request->flag == 'dish') {
            if (!empty($request->offer_id) || !empty($request->discount_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['error' => [$lang == 'en' ? 'When flag is dish, only dish_id should be sent.' : 'عندما تكون النوع طبق، يجب ارسال معرف الطبق فقط.']],
                    'validation_type' => true
                ], 400);
            }
            if (empty($request->dish_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['error' => [$lang == 'en' ? 'dish_id is required when flag is dish.' : 'معرف الطبق فقط مطلوب عندما تكون النوع طبق.']],
                    'validation_type' => true
                ], 400);
            }
        }
        if ($request->flag == 'discount') {
            if (!empty($request->dish_id) || !empty($request->offer_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['error' => [$lang == 'en' ? 'When flag is discount, only discount_id should be sent.' : 'عندما تكون النوع خصم، يجب ارسال معرف الخصم فقط.']],
                    'validation_type' => true
                ], 400);
            }
            if (empty($request->discount_id)) {
                return response()->json([
                    'code' => 400,
                    'status' => false,
                    'message' => 'Validation Error.',
                    'data' => null,
                    'errorData' => ['error' => [$lang == 'en' ? 'discount_id is required when flag is discount.' : 'معرف الخصم فقط مطلوب عندما تكون النوع خصم.']],
                    'validation_type' => true
                ], 400);
            }
        }
        // else
        // {
        //     return response()->json([
        //         'code' => 400,
        //         'status' => false,
        //         'message' => 'Validation Error.',
        //         'data' => null,
        //         'errorData' => ['error' => [$lang == 'en' ? $request->flag.'_id is required when flag is discount.' : $request->flag.'معرف فقط مطلوب عندما تكون النوع خصم.']],
        //         'validation_type' => true
        //     ], 400);
        // }

        $slider = $this->SliderService->store($request, null);

        return ResponseWithSuccessData($lang, $slider, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $exists = Slider::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $policy = $this->SliderService->show($id);

            return ResponseWithSuccessData($lang, $policy, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        try {

            if (!CheckTokenEmployee()) {
                return RespondWithBadRequest($lang, 5);
            }
            $exists = Slider::where('id', $id)->exists();

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }
            app()->setLocale($lang);
            $validator = Validator::make($request->all(), [
                'name_ar' => 'nullable|string|max:255',
                'name_en' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'description_ar' => 'nullable|string|max:255',
                'description_en' => 'nullable|string|max:255',
                'flag' => 'nullable',
                'dish_id' => 'nullable',
                'offer_id' => 'nullable',
                'discount_id' => 'nullable',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                    400,
                    $validator->errors()
                );
            }

            $data = $this->SliderService->update($request, $id);
            return ResponseWithSuccessData($lang, $data, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching recipe: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }
        $exists = Slider::where('id', $id)->exists();

        if (!$exists) {
            return respondError(__('branch_menu_category.not_found'), 404);
        }
        try {
            $response = $this->SliderService->destroy($id, $lang);

            // If response is a JSON error (bad request, etc.), return it directly
            if ($response instanceof \Illuminate\Http\JsonResponse) {
                return $response;
            }

            // Return success response
            $message = $lang === 'ar' ? 'تم حذف السلايدر بنجاح' : 'Slider deleted successfully';
            return ResponseWithSuccessData($lang, $message, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching Slider: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
