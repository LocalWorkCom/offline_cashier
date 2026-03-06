<?php

namespace App\Services\ClientServices;

use App\Events\UserRegistered;
use App\Models\ClientAddress;
use App\Models\Country;
use App\Models\User;
use DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ClientService
{

    public function registerClient(array $data, $guard)
    {
        //for website and client app
        $country = Country::where('phone_code', $data['country_code'])->first();

        if (!$country) {
            return respondError(
                'Validation Error.',
                400,
                [
                    'errorData' => [
                        'country_code' => [__('validation.phone_code', ['attribute' => __('auth.phone_code')])]
                    ]
                ]
            );
        }


        // try {
        $user = User::create([
            'name' => $data['name'],
            // 'email' => $data['email'],
            'flag' => 'client',
            'phone' => $data['phone'],
            'country_id' => $country->id,
            'country_code' => $data['country_code'],
            // 'birth_date' => $data['birth_date'] ?? null,
            'is_active' => 1,
            'password' => Hash::make($data['password']),
        ]);

        // event(new UserRegistered($user));



        return  $user;
        // } catch (\Exception $e) {
        //     return [
        //         'status' => 'error',
        //         'message' => __('An error occurred during registration. Please try again later.'),
        //         'code' => 500
        //     ];
        // }
    }
    public function getAllClients()
    {
        return User::query()
            ->clients()
            ->with(['country', 'activeAddresses']);
    }

    public function getClient($id)
    {
        return User::with(['country', 'activeAddresses'])->findOrFail($id);
    }

    public function createclient($data)
    {

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make('123456');
        $user->country_id = $data['country_id'];
        $user->country_code = $data['country_code'];
        $user->phone = $data['phone'];
        $user->birth_date = $data['birth_date'] ?? null;
        $user->flag = 'client';
        $user->is_active = $data['is_active'];
        $user->save();


        $clientAddress = new ClientAddress();
        $clientAddress->user_id = $user->id;
        $clientAddress->country_code = $user->country_code;
        $clientAddress->country_id = $data['address_country_id'];
        $clientAddress->address = $data['address'];
        $clientAddress->city_id = $data['city'];
        $clientAddress->area_id = $data['area'];
        $clientAddress->latitude = $data['latitude'] ?? null;
        $clientAddress->longtitude = $data['longtitude'] ?? null;
        $clientAddress->is_default =  $data['is_default'] ?? 1;
        $clientAddress->is_active = 1;
        $clientAddress->address_phone = $data['address_phone'] ?? null;
        $clientAddress->address_type = $data['address_type'] ?? null;
        $clientAddress->building = $data['building'] ?? null;
        $clientAddress->floor_number = $data['floor_number'] ?? null;
        $clientAddress->apartment_number = $data['apartment_number'] ?? null;

        $clientAddress->save();
        $user->load('activeAddresses');
        $user->refresh();
        return $user;
    }

    public function updateClient($data, $id)
    {
        $lang = app()->getLocale();

        $user = User::find($id);
        if(!$user){
            return false;
        }
        $user->name = $data['name'] ?? $user->name;
        $user->country_code = $data['country_code'] ?? $user->country_code;
        $user->email = $data['email'] ?? $user->email;
        $user->password = isset($data['password']) ? Hash::make($data['password']) : $user->password;
        $user->country_id = $data['country_id'] ?? $user->country_id;
        $user->country_code = $data['country_code'];
        $user->phone = $data['phone'] ?? $user->phone;
        $user->birth_date = $data['birth_date'] ?? $user->birth_date;
        $user->save();

        $user->refresh();
        return $user;
    }

    public function deleteClient($id)
    {
        $lang = app()->getLocale();

        $user = User::with('addresses')->find($id);

        if (!$user) {
            return false;
        }

        $user->addresses()->each(function ($address) {
            $address->delete();
        });

        $user->delete();
        return true;    
    }

    private function validateData($data)
    {
        $phone_length = Country::where('phone_code', $data['country_code'])->value('length');

        $validatedData = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            // 'password' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'country_code' => 'required|string',
            'phone' => [
                'required',
                'string',
                Rule::unique('users')->where(function ($query) use ($data) {
                    return $query->where('country_code', $data['country_code']);
                }),
                function ($attribute, $value, $fail) use ($data) {
                    $country = Country::where('phone_code', $data['country_code'])
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
            'is_active' => 'required|boolean',
            'address' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'postal_code' => 'nullable|string',
            'required' => [
                'nullable',
                'numeric',
                function ($attribute, $value, $fail) use ($phone_length) {
                    if ($phone_length && strlen($value) != $phone_length) {
                        $fail(__('validation.phone_length_invalid', ['length' => $phone_length]));
                    }
                },
            ],
            'is_default' => 'nullable|boolean'
        ]);

        if ($validatedData->fails()) {
            return respondError(__('validation.error'), 400, $validatedData->errors());
        }

        return $validatedData->validated(); // Returns valid array
    }
}
