<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Models\ClientAddress;
use App\Models\Country;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;

class ClientAddressApiController extends Controller
{

    public function index(Request $request)
    {
        // try {
        $lang = $request->header('lang', 'ar');
        app()->setLocale($lang);
        $user = auth('api')->user();
        if (!$user) {
            return RespondWithBadRequestData($lang, 2);
        }

        $addresses = ClientAddress::where('user_id', $user->id)
            ->where('is_active', 1)
            ->whereNull('deleted_at')
            ->get()
            ->map(function ($address) {
                $address->is_default = (bool) $address->is_default;
                return $address;
            })
            ->toArray();

        if (count($addresses) === 1) {
            $addresses[0]['is_default'] = true;
        }
        $filteredAddresses = count($addresses) ? array_map(function ($address) {
            $street_name = $this->extractStreetName($address['address']);
            $address = Arr::only($address, [
                'id',
                'user_id',
                'is_default',
                'latitude',
                'longtitude',
                'city',
                'state',
                'address_type',
                'building',
                'floor_number',
                'apartment_number',
                'notes',
                'country_code',
                'address_phone'
            ]);
            $address['street_name'] = $street_name;
            return $address;
        }, $addresses) : null;

        return ResponseWithSuccessData($lang, $filteredAddresses, 1);
        // } catch (\Exception $e) {
        //     // Log the exception for debugging purposes

        //     return RespondWithBadRequestData($lang, 2);
        // }
    }


    private function extractStreetName($address)
    {
        // Check if the address is valid
        if (!$address || !is_string($address)) {
            return 'Unknown'; // Return 'Unknown' if the address is invalid
        }

        // Split the address into parts based on commas or spaces
        $addressParts = explode(',', $address);

        // Assuming the street name is the first part of the address
        $streetName = trim($addressParts[0] ?? 'Unknown');

        return $streetName;
    }
    public function show(Request $request, $id)
    {
        try {
            // Get the language from the request header, default to 'ar'
            $lang = $request->header('lang', 'ar');

            // Set the application's locale for translations
            app()->setLocale($lang);

            // Authenticate the user
            $user = auth('api')->user();

            // If no user is authenticated, return an error
            if (!$user) {
                return RespondWithBadRequestData($lang, 2); // Unauthorized response
            }

            // Get the address by id for the authenticated user
            $address = ClientAddress::where('user_id', $user->id)
                ->findOrFail($id);
            $street_name = $this->extractStreetName($address->address);

            // Set is_default as boolean
            $address->is_default = (bool) $address->is_default;

            // Only return the necessary fields
            $filteredAddress = Arr::only($address->toArray(), [
                'id',
                'user_id',
                'is_default',
                // 'address',
                'latitude',
                'longtitude',
                'city',
                'state',
                'address_type',
                'building',
                'floor_number',
                'apartment_number',
                'notes',
                'country_code',
                'address_phone'
            ]);

            // Add the extracted street name to the response
            $filteredAddress['street_name'] = $street_name;

            return ResponseWithSuccessData($lang, $filteredAddress, 1);
        } catch (\Exception $e) {
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('api')->user();

            if (!$user) {
                return RespondWithBadRequestData($lang, 2);
            }

            App::setLocale($lang);

            $phone_length = Country::where('phone_code', $request->country_code)->value('length');

            // Validator with custom latitude and longitude validation
            $validator = Validator::make($request->all(), [
                // 'address' => 'required|string',
                'street_name' => 'required|string',
                'city' => 'nullable|string',
                'state' => 'required|string',
                //'postal_code' => 'nullable|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'is_default' => 'nullable',
                'is_active' => 'nullable',
                'address_type' => 'required|in:apartment,villa,office',
                'building' => 'required',
                'floor_number' => [
                    'required_if:address_type,apartment,office',
                ],

                'apartment_number' => 'required',
                'notes' => 'nullable|string',
                'country_code' => 'required',
                'address_phone' => 'required|numeric',
            ]);

            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }

            if ($phone_length && strlen($request->address_phone) != $phone_length) {
                return respondError('Validation Error.', 400, [
                    'address_phone' => [
                        __('validation.custom.phone.length', [
                            'attribute' => __('auth.phone'),
                            'length' => $phone_length,
                        ])
                    ]
                ]);
            }

            // Check if the latitude and longitude are valid numeric values
            $latitude = $request->latitude;
            $longitude = $request->longitude;

            if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
                return respondError(trans('validation.invalid_latitude'), 400);
            }

            if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
                return respondError(trans('validation.invalid_longitude'), 400);
            }

            // // Determine if the request specifies 'is_default'
            // $isDefault = $request->has('is_default') && filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            // // If is_default is 1, update all existing addresses for this user to make them non-default
            // if ($isDefault === 1) {
            //     ClientAddress::where('user_id', $user->id)
            //         ->whereNull('deleted_at')  // To exclude soft-deleted addresses
            //         ->update(['is_default' => 0]);  // Set all user's addresses to non-default
            // }
            // // $is_active = $request->has('is_active') && filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            ClientAddress::where('user_id', $user->id)->update(['is_default' => 0]);

            // Create new address and set default value accordingly
            $clientAddress = new ClientAddress();
            $clientAddress->user_id = $user->id;
            $clientAddress->latitude = $latitude;
            $clientAddress->longtitude = $longitude;
            $clientAddress->address = $request->street_name;
            // $clientAddress->address = $request->address;
            // $clientAddress->city = $request->city;
            // $clientAddress->state = $request->state;
            $clientAddress->address_type = $request->address_type;
            $clientAddress->building = $request->building;
            $clientAddress->floor_number = $request->floor_number;
            $clientAddress->apartment_number = $request->apartment_number;
            $clientAddress->notes = $request->notes;
            $clientAddress->country_code = $request->country_code ?? $user->country_code;
            $clientAddress->address_phone = $request->address_phone ?? $user->phone;
            // $clientAddress->postal_code = $request->postal_code;
            $clientAddress->is_default = 1;
            $clientAddress->is_active = $request->is_active ?? 1;
            $clientAddress->save();
            $clientAddress->is_default = (bool) $clientAddress->is_default;
            $clientAddress->is_active = (bool) $clientAddress->is_active;

            return ResponseWithSuccessData($lang, $clientAddress, 1);
        } catch (\Exception $e) {
            Log::error('Error creating address: ' . $e->getMessage());
            return respondError($e->getMessage(), 2);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $user = auth('api')->user();

            if (!$user) {
                return RespondWithBadRequestData($lang, 2);
            }

            App::setLocale($lang);

            // Retrieve phone length from the Country table based on the country code
            $phone_length = Country::where('phone_code', $request->country_code)->value('length');

            // Find the existing address by ID
            $clientAddress = ClientAddress::find($id);

            if (!$clientAddress) {
                // return respondError(trans('validation.address_not_found'), 404);
                return respondError('Validation Error.', 400, ['error'=>trans('validation.address_not_found')]);
            }

            // Check if the logged-in user is the owner of the address
            if ($clientAddress->user_id != $user->id) {
                // return respondError(trans('validation.unauthorized_access'), 403);
                return respondError('Validation Error.', 400, ['error'=>trans('validation.unauthorized_access')]);
            }

            // Validate the incoming request
            $validator = Validator::make($request->all(), [
                // 'address' => 'required|string',
                'street_name' => 'required|string',
                'city' => 'required|string',
                'state' => 'required|string',
                'latitude' => 'required|numeric|between:-90,90',
                'longitude' => 'required|numeric|between:-180,180',
                'is_default' => 'nullable',
                'is_active' => 'nullable',
                'address_type' => 'required|in:apartment,villa,office',
                'building' => 'required',
                'floor_number' => [
                    'required_if:address_type,apartment,office',
                ],
                'apartment_number' => 'required',
                'notes' => 'nullable|string',
                'country_code' => 'required',
                'address_phone' => 'required|numeric',
            ]);

            // If basic validation fails, return errors
            if ($validator->fails()) {
                return respondError('Validation Error.', 400, $validator->errors());
            }

            // Handle custom phone length validation based on the country
            if ($phone_length && strlen($request->address_phone) != $phone_length) {
                return respondError('Validation Error.', 400, [
                    'address_phone' => [
                        __('validation.custom.phone.length', [
                            'attribute' => __('auth.phone'),
                            'length' => $phone_length,
                        ])
                    ]
                ]);
            }
            $isDefault = $request->has('is_default')
                ? (filter_var($request->is_default, FILTER_VALIDATE_BOOLEAN) ? 1 : 0)
                : null;

            // If is_default is provided and set to 1, reset other default addresses
            if ($isDefault === 1) {
                ClientAddress::where('user_id', $user->id)
                    ->whereNull('deleted_at')  // Only consider active (non-deleted) addresses
                    ->update(['is_default' => 0]);  // Set all the user's addresses to non-default
            }
            // Update the client address with the new data
            // $clientAddress->address = $request->address;
            $clientAddress->address = $request->street_name;
            $clientAddress->city = $request->city;
            $clientAddress->state = $request->state;
            // $clientAddress->postal_code = $request->postal_code;
            $clientAddress->latitude = $request->latitude;
            $clientAddress->longtitude = $request->longitude;
            $clientAddress->address_type = $request->address_type;
            $clientAddress->building = $request->building;
            $clientAddress->floor_number = $request->floor_number;
            $clientAddress->apartment_number = $request->apartment_number;
            $clientAddress->notes = $request->notes;
            $clientAddress->country_code = $request->country_code ?? $user->country_code;
            $clientAddress->address_phone = $request->address_phone ?? $user->phone;
            $clientAddress->is_default = $isDefault !== null ? $isDefault : $clientAddress->is_default;
            $clientAddress->is_active = $request->is_active ?? 1;
            $clientAddress->save();
            $clientAddress->is_default = (bool) $clientAddress->is_default;
            $clientAddress->is_active = (bool) $clientAddress->is_active;

            return ResponseWithSuccessData($lang, $clientAddress, 1);
        } catch (\Exception $e) {
            Log::error('Error updating address: ' . $e->getMessage());
            return respondError($e->getMessage(), 2);
        }
    }



    public function destroy(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $address = ClientAddress::findOrFail($id);
            $address->update(['deleted_by' => auth()->id()]);
            $address->save();

            return ResponseWithSuccessData($lang, null, 1);
        } catch (\Exception $e) {
            Log::error('Error deleting address: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
    public function makeDefault(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');

            // Find the address and mark it as default
            $address = ClientAddress::withTrashed()->findOrFail($id);

            // Reset other default addresses for the user
            ClientAddress::where('user_id', $address->user_id)
                ->where('id', '!=', $id)
                ->where('is_default', 1)
                ->update(['is_default' => 0]);

            // Set the current address as default
            $address->is_default = 1;
            $address->save();
            $address->is_default = (bool) $address->is_default;

            return ResponseWithSuccessData($lang, $address, 1);
        } catch (\Exception $e) {
            Log::error('Error setting default address: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request, $id)
    {
        try {
            $lang = $request->header('lang', 'ar');
            $address = ClientAddress::withTrashed()->findOrFail($id);
            $address->restore();

            return ResponseWithSuccessData($lang, $address, 1);
        } catch (\Exception $e) {
            Log::error('Error restoring brand: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
