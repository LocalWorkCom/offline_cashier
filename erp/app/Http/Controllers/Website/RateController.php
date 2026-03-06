<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoFormRequest;
use App\Models\Logo;
use App\Services\ClientServices\RateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class RateController extends Controller
{
    protected $rateService;

    public function __construct(RateService $rateService)
    {
        $this->rateService = $rateService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
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
        $response = $this->rateService->save($request);
        $responseData = $response->original;

        if (!$responseData['status']) {
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                // Return JSON response for AJAX requests
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => false,
                        'errors' => $responseData['data']
                    ], 400);
                }
                // For regular form submissions
                return redirect()->back()->withErrors($responseData['data'])->withInput();
            }
            // For other types of errors
            $message = $responseData['message'];
            return redirect()->back()->with('error', $message);
        }

        // Success case
        $message = $responseData['message'];

        return redirect()->route('home')->with([
            'show_confirmation_modal' => true,
            'success' => $message
        ]);
    }
    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
