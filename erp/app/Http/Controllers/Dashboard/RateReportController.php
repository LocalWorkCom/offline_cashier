<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\LogoFormRequest;
use App\Models\Logo;
use App\Services\ClientServices\RateOldService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class RateReportController extends Controller
{
    protected $rateService;

    public function __construct(RateOldService $rateService)
    {
        $this->rateService = $rateService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $response = $this->rateService->index();

        $responseData = $response->original;

        $rates = $responseData['data'];

        return view('dashboard.reports.rates.list', compact('rates'));
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

        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect()->route('reports.rates.list')->with('message',$message);
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
        $response = $this->rateService->save($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect()->route('reports.rates.list')->with('message',$message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $response = $this->rateService->destroy($id);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect()->route('reports.rates.list')->with('message',$message);
    }
}
