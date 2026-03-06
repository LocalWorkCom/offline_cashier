<?php

namespace App\Http\Controllers\Api\DashboardAPIs;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Country;
use App\Models\Order;
use App\Models\User;
use App\Services\ClientServices\ClientService;
use App\Services\ReportServices\CustomerServiceDeliveryOrdersReportsService;
use App\Services\OrdersReportsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientApiController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $query = $this->clientService->getAllClients();
        $response = paginateOrGetAll($query, $request);
        $response['data'] = ClientResource::collection($response['data']);
        return ResponseWithSuccessDataPaginated($lang, $response, 1);
    }
    public function show(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        try {
            $client = new ClientResource($this->clientService->getClient($id));

            return ResponseWithSuccessData($lang, $client, 1);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $message = $lang === 'ar' ? 'العميل غير موجود' : 'Client not found';
            return respondError($message, 404);
        } catch (\Exception $e) {
            Log::error('Error fetching client: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function store(Request $request)
    {

        $lang = $request->header('lang', 'ar');
        $phone_length = Country::where('phone_code', $request->country_code)->value('length');

        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at'),
            ],            // 'password' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'country_code' => 'required|string',
            'phone' => [
                'required',
                'string',
                Rule::unique('users')->whereNull('deleted_at')->where(function ($query) use ($request) {
                    return $query->where('country_code', $request->country_code);
                }),
                function ($attribute, $value, $fail) use ($request) {
                    $country = DB::table('countries')
                        ->where('phone_code', $request->country_code)
                        ->first();

                    if (!$country) {
                        $fail(__('validation.country_code_invalid'));
                    }

                    if (isset($country->length) && strlen($value) != $country->length) {
                        $fail(__('validation.custom.phone.length', ['attribute' => __('auth.phone'), 'length' => $country->length]));
                    }
                },
            ],
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'birth_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'address' => 'required|string',
            'address_country_id' => 'required|exists:countries,id',
            'city' => 'required|string',
            'area' => 'required|string',
            'address_phone' => 'required|string',
            'apartment_number' => 'required|string',
            'floor_number' => 'required_if:address_type,apartment,office|string',
            'building' => 'required|string',
            'hotel_id' => 'required_if:address_type,hotel|exists:hotels,id',
            'address_type' => 'required|string|in:apartment,villa,office,hotel',
            'is_default' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }
        $validatedData = $validator->validated();

        $client = $this->clientService->createclient($validatedData);
        // If the service returned a JsonResponse (error), return it directly
        if ($client instanceof \Illuminate\Http\JsonResponse) {
            return $client;
        }

        return ResponseWithSuccessData($lang, $client, 1);
    }
    public function update(Request $request, $id)
    {

        $lang = $request->header('lang', 'ar');
        $phone_length = Country::where('phone_code', $request->country_code)->value('length');

        $user = User::find($id);
        if(!$user){
            $message = $lang === 'ar' ? 'العميل غير موجود' : 'Client not found';
            return respondError($message, 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string',
            'email' => 'required|email|unique:users,email,' . $id,
            // 'password' => 'nullable|string',
            'country_id' => 'nullable|exists:countries,id',
            'phone' => [
                'required',
                'numeric',
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.custom.phone.length', ['attribute' => __('auth.phone'), 'length' => $phone_length]));
                    }
                },
            ],
            'country_code' => 'required|string',
            'birth_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            // 'address' => 'nullable|string',
            // 'city' => 'nullable|string',
            // 'state' => 'nullable|string',
            // 'postal_code' => 'nullable|string',
            // 'address_phone' => [
            //     'nullable',
            //     'numeric',
            //     function ($attribute, $value, $fail) use ($phone_length) {
            //         if ($phone_length && strlen($value) != $phone_length) {
            //             $fail(__('validation.phone_length_invalid', ['length' => $phone_length]));
            //         }
            //     },
            // ],
            // 'is_default' => 'nullable|boolean'
        ]);
        if ($validator->fails()) {
            return respondError(
                $lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.',
                400,
                $validator->errors()
            );
        }
        $validatedData = $validator->validated();


        $client = $this->clientService->updateClient($validatedData, $id);

        if(!$client){
            $message = $lang === 'ar' ? 'العميل غير موجود' : 'Client not found';
            return respondError($message, 404);
        }

        // If the service returned a JsonResponse (error), return it directly
        if ($client instanceof \Illuminate\Http\JsonResponse) {
            return $client;
        }

        return ResponseWithSuccessData($lang, $client, 1);
    }
    public function destroy(Request $request, $id)
    {
        $lang = $request->header('lang', 'ar');

        $result = $this->clientService->deleteClient($id);
        if(!$result){
            $message = $lang === 'ar' ? 'العميل غير موجود' : 'Client not found';
            return respondError($message, 404);
        }
        return ResponseWithSuccessData($lang, null, 1);
    }
}
