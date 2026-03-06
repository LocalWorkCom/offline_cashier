<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Area;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\AreaService;

class RegionsController extends Controller
{
    protected $areaService;


    public function __construct(AreaService $areaService)
    {
        $this->areaService = $areaService;
    }

    public function index(Request $request, $city)
    {
        $response = $this->areaService->index($request, $city);

        // Check if it's a response object
        if (
            $response instanceof \Illuminate\Http\JsonResponse ||
            $response instanceof \Illuminate\Http\Response
        ) {
            $responseData = $response->original;
            $regions = $responseData['data'] ?? [];
        }
        // Check if it's an Eloquent builder
        elseif ($response instanceof \Illuminate\Database\Eloquent\Builder) {
            $regions = $response->get(); // Execute the query
        }
        // Check if it's already a collection
        elseif ($response instanceof \Illuminate\Database\Eloquent\Collection) {
            $regions = $response;
        }
        // Handle other cases
        else {
            $regions = [];
        }
        $regions = Area::where('city_id', $city)->get();

        return view('dashboard.country.regions.list', compact('regions', 'city'));
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
        $response = $this->areaService->store($request);

        // Check if response exists and has original property
        if (!$response || !is_object($response) || !property_exists($response, 'original')) {
            return redirect()->back()->with('error', 'Unexpected response from service')->withInput();
        }

        $responseData = $response->original;

        // Check if responseData is an array and has the expected structure
        if (!is_array($responseData) || !isset($responseData['status'])) {
            return redirect()->back()->with('error', 'Invalid response format from service')->withInput();
        }

        if (!$responseData['status']) {
            $validationErrors = $responseData['data'] ?? [];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }

        $message = $responseData['message'] ?? 'Area created successfully';
        return redirect()->route('region.list', ['id' => $request->city_id])->with('message', $message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function show_all($city_id)
    {
        $response = $this->areaService->show_all($city_id);
        $responseData = $response->original;
        $regions = $responseData['data'];
        // Check if the request is AJAX
        if (request()->ajax()) {
            return response()->json($regions);
        }
        return view('dashboard.country.regions.show_all', compact('regions'));
    }

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
        $response = $this->areaService->update($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('region.list', ['id' => $request->city_id])->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->areaService->destroy($id);
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
