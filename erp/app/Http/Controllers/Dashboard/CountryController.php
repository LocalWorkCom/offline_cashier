<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Services\SettingsServices\CountryService;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    protected $countryService;
    protected $checkToken;  // Set to true or false based on your need


    public function __construct(CountryService $countryService)
    {
        $this->countryService = $countryService;
        $this->checkToken = false;
    }

    public function index(Request $request)
    {
        // $response = $this->countryService->index($request, $this->checkToken);

        // $responseData = $response->original;

        // $countries = $responseData['data'];
        $countries = Country::whereNull('deleted_at')
            ->orderBy('order', 'Asc')
            ->get();
        return view('dashboard.country.list', compact('countries'));
    }

    public function show($id)
    {
        $response = $this->countryService->show($id);
        $responseData = $response->original;
        return $country = $responseData['data'];
    }

    public function store(Request $request)
    {
        $response = $this->countryService->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('countries.list')->with('message', $message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->countryService->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('countries.list')->with('message', $message);
    }
    public function destroy(Request $request, $id)
    {
        $response = $this->countryService->destroy($request, $id, $this->checkToken);
        $responseData = $response->original;
        // AJAX response for validation errors
        if (!$responseData['status']) {
            return response()->json([
                'status' => false,
                'message' => $responseData['message'],
                'errors' => $responseData['data'] ?? null
            ], 400);
        }

        // Success response
        return response()->json([
            'status' => true,
            'message' => $responseData['message']
        ]);
    }
    public function checkOrder(Request $request)
    {
        $order = $request->input('order');

        $country = Country::where('order', $order)->first();

        if ($country) {
            return response()->json([
                'exists' => true,
                'id' => $country->id,
                'name' => app()->getLocale() === 'ar' ? $country->name_ar : $country->name_en,
            ]);
        }

        return response()->json(['exists' => false]);
    }
}
