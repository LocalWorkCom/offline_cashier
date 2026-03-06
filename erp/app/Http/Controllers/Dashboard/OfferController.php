<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Offer;
use App\Models\OrderDetail;
use App\Models\Slider;
use App\Services\SettingsServices\OfferService;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    protected $offerService;

    public function __construct(OfferService $offerService)
    {
        $this->offerService = $offerService;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $response = $this->offerService->index();

        $responseData = $response->original;

        $offers = $responseData['data'];
        $branches = Branch::get();

//        dd($offers);
        return view('dashboard.offer.list', compact('offers', 'branches'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::get();
        return view('dashboard.offer.add', compact('branches'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        dd($request->all());
        $response = $this->offerService->save($request);
        $responseData = $response->original;
//        dd($responseData);
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect()->route('offers.list')->with('message',$message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $response = $this->offerService->show($id);

        $responseData = $response->original;

        $offer = $responseData['data']['offer'];
        $branches = $responseData['data']['branches'];
//        dd($offer);

        return view('dashboard.offer.show', compact('offer', 'branches'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $offer = Offer::findOrFail($id);
        $branches = Branch::all(); // Assuming you fetch all branches
        $selectedBranches = explode(',', $offer->branch_id);
        return view('dashboard.offer.edit', compact('offer', 'branches', 'selectedBranches'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $response = $this->offerService->save($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect()->route('offers.list')->with('message',$message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
//        $isLinkedToSlider = Slider::where('offer_id', $id)->exists();
//
//        if ($isLinkedToSlider) {
//            return redirect()->back()->withErrors(__('validation.offer_linked_error'));
//        }
        $isLinkedToOrder = OrderDetail::where('offer_id', $id)->exists();

        if ($isLinkedToOrder) {
            return redirect()->back()->withErrors(__('validation.offer_linked_order_error'));
        }
        $response = $this->offerService->destroy($id);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect()->route('offers.list')->with('message',$message);
    }
}
