<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\VehicleSetting;
use App\Services\SettingsServices\VehicleSettingService;
use Illuminate\Http\Request;

class VehicleSettingController extends Controller
{
    protected $VehicleSettingService;
    protected $checkToken;
    protected $lang;

    public function __construct(VehicleSettingService $VehicleSettingService)
    {
        $this->VehicleSettingService = $VehicleSettingService;
        $this->checkToken = false;
        $this->lang = app()->getLocale();
    }

    public function index(Request $request)
    {
        $response = $this->VehicleSettingService->index($request);
        // $responseData = $response->original;
        $VehicleSettings = $response['data'];

        // $VehicleSettings = VehicleSetting::hydrate($responseData['data']);
        return view('dashboard.VehicleSetting.list', compact('VehicleSettings'));
    }
    public function edit($id)
    {
        $vehicleSetting = VehicleSetting::findOrFail($id);
        return view('dashboard.VehicleSetting.edit', compact('vehicleSetting'));
    }
    
    public function update(Request $request, $id)
    {
        $response = $this->VehicleSettingService->update($request, $id, $this->checkToken);
        $responseData = $response->original;

        if (!$responseData['status'] && isset($responseData['data'])) {
            if (isset($responseData['data'])) {
                $validationErrors = $responseData['data'];
                return redirect()->back()->withErrors($validationErrors)->withInput();
            } else {
                // dd(0);
                return redirect()->back()->withErrors($responseData['message'])->withInput();
            }
        }

        $message = $responseData['message'];
        return redirect()->route('vehicle_settings.index')->with('message', $message);
    }

}

