<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Size;
use App\Services\StoreServices\SizeService;
use Illuminate\Http\Request;

class SizeController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $SizeService;
    protected $checkToken;
    protected $lang;

    public function __construct(SizeService $SizeService)
    {
        $this->SizeService = $SizeService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {

        // Pass it to the service
        $response  = $this->SizeService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $Sizes = Size::hydrate($responseData['data']);
        $categories = Category::all();

        return view('dashboard.size.list', compact('Sizes','categories'));
    }

    public function store(Request $request)
    {
        $response = $this->SizeService->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/sizes')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/sizes')->with('message',$message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->SizeService->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/sizes')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/sizes')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->SizeService->delete($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/sizes')->with('message',$message);
    }
}
