<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\City;
use App\Models\Country;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\CitiesService;

class CitiesController extends Controller
{
    protected $cityService;


    public function __construct(CitiesService $cityService)
    {
        $this->cityService = $cityService;
    }



    public function index(Request $request, $country)
    {
        // Get country with its cities
        $countryWithCities = $this->cityService->getCountryWithCities($country, $request);

        if (!$countryWithCities) {
            // Handle case where country doesn't exist
            return redirect()->back()->with('error', 'Country not found');
        }

        $cities = $countryWithCities->cities;

        return view('dashboard.country.cities.list', compact('cities', 'country', 'countryWithCities'));
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $response = $this->cityService->store($request);

        // Check if response is an object and has the original property
        if (is_object($response) && property_exists($response, 'original')) {
            $responseData = $response->original;
        }
        // Check if response is already an array (direct data)
        elseif (is_array($response)) {
            $responseData = $response;
        }
        // Handle unexpected response types
        else {
            return redirect()->back()->with('error', 'Unexpected response from service')->withInput();
        }

        // Check if responseData is an array and has the expected keys
        if (!is_array($responseData) || !isset($responseData['status'])) {
            return redirect()->back()->with('error', 'Invalid response format')->withInput();
        }

        if (!$responseData['status']) {
            $validationErrors = $responseData['data'] ?? [];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }

        $message = $responseData['message'] ?? 'City created successfully';
        return redirect()->route('city.list', ['id' => $request->country_id])->with('message', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function show_all($country_id)
    {
        // Check if the parameter is a phone code (string) or country ID (numeric)
        if (is_numeric($country_id)) {
            // If it's numeric, check if it's a phone code or country ID
            $country = Country::where('id', $country_id)->first();
            if (!$country) {
                // If no country found by ID, try phone code
                $country = Country::where('phone_code', $country_id)->first();
                if ($country) {
                    $country_id = $country->id;
                }
            }
        } else {
            // If it's not numeric, treat it as phone code
            $country = Country::where('phone_code', $country_id)->first();
            if ($country) {
                $country_id = $country->id;
            }
        }

        $cities = City::where('country_id', $country_id)->get();

        return response()->json($cities);
    }
    //  public function show_all($country_id)
    // {
    //     $country_id = Country::where('phone_code',$country_id)->first()->id; // Ensure $country_id is defined
    //     $response = $this->cityService->show_all($country_id);
    //     $responseData = $response->original;
    //     $cities = $responseData['data'];

    //     // Check if the request is AJAX
    //     if (request()->ajax()) {
    //         return response()->json($cities);
    //     }

    //     // Otherwise, return a Blade view (for normal access)
    //     return view('dashboard.country.cities.show_all', compact('cities'));
    // }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $response = $this->cityService->update($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('city.list', ['id' => $request->country_id])->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->cityService->destroy($id);
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
}
