<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Color;
use App\Services\StoreServices\ColorService;
use Illuminate\Http\Request;

class ColorController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $ColorService;
    protected $checkToken;
    protected $lang;

    public function __construct(ColorService $ColorService)
    {
        $this->ColorService = $ColorService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {

        // Pass it to the service
        $response  = $this->ColorService->index($request);
        // $responseData = $response->original;
        $Colors = $response['data'];

        // $responseData = json_decode($response->getContent(), true);
        // $Colors = Color::hydrate($responseData['data']['data']);

        return view('dashboard.color.list', compact('Colors'));
    }

    public function store(Request $request)
    {
        $response = $this->ColorService->store($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['errorData'])) {
            $validationErrors = $responseData['errorData'];
            return redirect('dashboard/colors')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/colors')->with('message',$message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->ColorService->update($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['errorData'])) {
            $validationErrors = $responseData['errorData'];
            return redirect('dashboard/colors')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/colors')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $request['lang'] = app()->getLocale();
        $response = $this->ColorService->delete($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['errorData'])) {
            $validationErrors = $responseData['errorData'];
            return redirect('dashboard/colors')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/colors')->with('message',$message);
    }
}
