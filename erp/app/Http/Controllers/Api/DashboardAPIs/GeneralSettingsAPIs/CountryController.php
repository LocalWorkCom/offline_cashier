<?php

namespace App\Http\Controllers\Api\DashboardAPIs\GeneralSettingsAPIs;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Models\User;
use App\Services\SettingsServices\CountryService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CountryController extends Controller
{
    protected $countryService;
    protected $checkToken;

    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
        $this->checkToken = false;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');

        try {
            // Get the query builder from the service
            $countriesQuery = $this->countryService->index($request, $this->checkToken);

            // Apply pagination or get all results
            $result = paginateOrGetAll($countriesQuery, $request, null);

            // Check if the result is paginated (has 'data' key) or a simple array/collection
            if (isset($result['data'])) {
                // It's a paginated response
                $transformedData = collect($result['data'])->map(function ($country) use ($lang) {
                    return [
                        'id' => $country->id,
                        'name' => $lang == 'en' ? $country->name_en : $country->name_ar,
                        'phone_code' => $country->phone_code,
                        'currency' => $lang == 'en' ? $country->currency_en : $country->currency_ar,
                        'currency_ar'=> $country->currency_ar,
                        'currency_en'=> $country->currency_en,
                        'name_ar'=> $country->name_ar,
                        'name_en'=> $country->name_en,
                        'currency_symbol'=> $country->currency_symbol,
                        'currency_code'=> $country->currency_code,
                        'city'=> $country->cities->count(),
                        'active_show' => $country->active_show == 1
                            ? ($lang == 'ar' ? 'نشط' : 'Active')
                            : ($lang == 'ar' ? 'غير نشط' : 'Inactive'),
                        'order' => $country->order == 0
                            ? ($lang == 'ar' ? 'بدون ترتيب' : 'No Order')
                            : $country->order,
                        'image' => $country->flag ?? null,
                        'length' => $country->length ?? 10,
                        'code' => $country->code ?? null,
                    ];
                });

                // Replace the data in the result array
                $result['data'] = $transformedData;

                // Use your custom response function for paginated data
                return ResponseWithSuccessDataPaginated($lang, $result, 1);
            } else {
                // It's a simple collection/array
                $transformedData = collect($result)->map(function ($country) use ($lang) {
                    return [
                        'id' => $country->id,
                        'name' => $lang == 'en' ? $country->name_en : $country->name_ar,
                        'phone_code' => $country->phone_code,
                        'currency' => $lang == 'en' ? $country->currency_en : $country->currency_ar,
                        'image' => $country->flag ?? null,
                        'length' => $country->length ?? 10,
                        'code' => $country->code ?? null,
                    ];
                });

                return ResponseWithSuccessData($lang, $transformedData, 1);
            }
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    // public function index(Request $request)
    // {
    //     $lang = $request->header('lang', 'ar');

    //     $response = $this->countryService->index($request, $this->checkToken);

    //     $responseData = $response->original;

    //     $countries = $responseData['data'];
    //     $countries = $countries->map(function ($country) use ($lang) {
    //         return [
    //             'id' => $country->id,
    //             'name' => $lang == 'en' ? $country->name_en : $country->name_ar,
    //             'phone_code' => $country->phone_code,
    //             'currency' => $lang == 'en' ? $country->currency_en : $country->currency_ar,
    //             'image' => $country->flag ?? null,
    //             'length' =>  $country->length ?? 10,
    //             'code'=>$country->code ?? null,
    //         ];
    //     });

    //     return ResponseWithSuccessData($lang, $countries, 1);
    // }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = Country::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $Country = Country::findOrFail($id);
            return ResponseWithSuccessData($lang, $Country, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {
        $lang = $request->header('lang', 'en');

        try {
            $result = $this->countryService->store($request, false);

            // Check if result is an array (error response)
            if (is_array($result) && isset($result['code'])) {
                // Return the error response directly without wrapping
                return response()->json($result, $result['code']);
            }
            if (isset($result->original['code']) && $result->original['code'] == 400) {
                // Return the error response directly without wrapping
                return $result;
            }
            // If it's a BusinessActivity object, return success response
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $result = $this->countryService->update($request, $id, false);

            // Check if result is an array (error response)
            if (is_array($result) && isset($result['code'])) {
                // Return the error response directly without wrapping
                return response()->json($result, $result['code']);
            }

            // If it's a BusinessActivity object, return success response
            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return response()->json([
                'code' => 500,
                'status' => false,
                'message' => 'Internal server error',
                'data' => null,
                'errorData' => ['error' => $e->getMessage()],
                'validation_type' => false
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $exists = Country::where('id', $id)->exists();
            App::setLocale($lang);

            if (!$exists) {
                return respondError(__('branch_menu_category.not_found'), 404);
            }

            $response = $this->countryService->destroy($request, $id, false);
            return $response;
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function showList()
    {
        $lang = request()->header('lang', 'ar');
        if (!CheckTokenEmployee()) {
            return RespondWithBadRequest($lang, 5);
        }

        return $data = $this->countryService->showList(request());
    }
}
