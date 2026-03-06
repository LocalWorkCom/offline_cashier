<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Gift;
use App\Services\SettingsServices\GiftService;
use Illuminate\Http\Request;

class GiftController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $giftService;
    protected $checkToken;
    protected $lang;

    public function __construct(GiftService $giftService)
    {
        $this->giftService = $giftService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {

        $response  = $this->giftService->index($request);
        $responseData = json_decode($response->getContent(), true);
        $gifts = Gift::hydrate($responseData['data']);

        return view('dashboard.gift.list', compact('gifts'));
    }

    public function store(Request $request)
    {
        $response = $this->giftService->store($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/gifts')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/gifts')->with('message',$message);
    }

    public function update(Request $request, $id)
    {
        $response = $this->giftService->update($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect('dashboard/gifts')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect('dashboard/gifts')->with('message',$message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->giftService->destroy($request, $id);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('gifts')->with('message',$message);
    }
}
