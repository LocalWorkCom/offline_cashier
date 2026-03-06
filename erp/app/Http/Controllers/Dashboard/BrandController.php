<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Product;
use App\Services\StoreServices\BrandService;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $BrandService;
    protected $checkToken;  // Set to true or false based on your need
    protected $lang;  // Set to true or false based on your need


    public function __construct(BrandService  $BrandService)
    {
        $this->BrandService  = $BrandService;
        $this->checkToken = false;
        $this->lang =  app()->getLocale();
    }

    public function index(Request $request)
    {


        // Pass it to the service
        $response  = $this->BrandService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $brands = Brand::hydrate($responseData['data']);

        return view('dashboard.brand.list', compact('brands'));
    }

    // public function create()
    // {

    //     // Pass it to the service
    //     // $response  = $this->BrandService ->create($request, $this->checkToken);
    //     // $responseData = json_decode($response->getContent(), true);
    //     // $products = $responseData['data'];
    //     return view('dashboard.brand.add');
    // }
    public function create()
    {
        // $brands = Brand::where('active', 1)->get();
        return view('dashboard.brand.add');
    }

    public function store(Request $request)
    {
        $response = $this->BrandService->store($request, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('brands.list')->with('message', $message);
    }
    
    public function show($id)
    {
        $brand = Brand::findOrFail($id);
        //        dd($category);
        return view('dashboard.brand.show', compact('brand','id'));
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);
        $brands = Brand::all(); // Fetch only active categories
        // $brands = Brand::where('active', 1)->get(); // Fetch only active categories

        return view('dashboard.brand.edit', compact('brand', 'brands','id'));
    }

    public function update(Request $request, $id)
    {
        $response =  $this->BrandService->update($request, $id, $this->checkToken);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/brands')->with('message',$message);

    }
    public function delete(Request $request, $id)
    {
        $response = $this->BrandService->destroy($request, $id, $this->checkToken);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/brands')->with('message',$message);

    }

}
