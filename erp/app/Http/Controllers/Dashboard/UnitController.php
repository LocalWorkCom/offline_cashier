<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Unit;
use App\Services\StoreServices\UnitService;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $UnitService;
    protected $checkToken;
    protected $lang;

    public function __construct(UnitService $UnitService)
    {
        $this->UnitService = $UnitService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {

        // Pass it to the service
        $response  = $this->UnitService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $Units = Unit::hydrate($responseData['data']);

        return view('dashboard.unit.list', compact('Units'));
    }

    public function store(Request $request)
    {
        $response = $this->UnitService->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('units')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/units')->with('message',$message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->UnitService->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/units')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/units')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->UnitService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/units')->with('message',$message);
    }
}
