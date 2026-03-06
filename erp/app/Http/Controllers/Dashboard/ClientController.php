<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\User;
use App\Services\ClientServices\ClientService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function index()
    {
        $users = $this->clientService->getAllClients()->get();
        return view('dashboard.clients.index', compact('users'));
    }
    public function show($id)
    {
        $client = $this->clientService->getClient($id);
        return view('dashboard.clients.show', compact('client'));
    }

    public function create()
    {
        $countries = Country::all();
        return view('dashboard.clients.create', compact('countries'));
    }
    public function store(Request $request)
    {
        $phone_length = Country::where('phone_code', $request->country_code)->value('length');

        $validatedData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            // 'password' => 'nullable|string',
            'country_id' => 'required|exists:countries,id',
            'country_code' => 'required|string',
            'phone' => [
                'required',
                'string',
                Rule::unique('users')->where(function ($query) use ($request) {
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


        $this->clientService->createClient($validatedData);
        return redirect()->route('client.index')->with('success', 'Client created successfully!');
    }

    public function edit($id)
    {
        $client = User::where('flag', 'client')->with('addresses')->findOrFail($id);
        $countries = Country::all();
        return view('dashboard.clients.edit', compact('countries', 'client'));
    }

    public function update(Request $request, $id)
    {
        $phone_length = Country::where('phone_code', $request->country_code)->value('length');

        $validatedData = $request->validate([
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
            'image' => 'nullable|mimes:jpeg,png,jpg,gif,svg',
            'birth_date' => 'nullable|date',
            'is_active' => 'nullable|boolean',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'postal_code' => 'nullable|string',
            'address_phone' => [
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

        $this->clientService->updateClient($validatedData, $id, $this->checkToken);
        return redirect()->route('client.index')->with('success', 'Client updated successfully!');
    }
    public function destroy($id)
    {
        $this->clientService->deleteClient($id, $this->checkToken);
        return redirect()->route('client.index')->with('success', 'Client deleted successfully!');
    }
}
