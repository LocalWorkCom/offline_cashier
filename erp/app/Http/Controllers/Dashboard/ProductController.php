<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Size;
use App\Models\Unit;
use App\Models\Brand;
use App\Models\Color;
use App\Models\Store;

use App\Models\Country;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductUnit;
use App\Models\ProductLimit;
use App\Models\ProductStore;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\StoreServices\UnitService;
use App\Services\StoreServices\BrandService;
use App\Services\StoreServices\ProductService;
use App\Services\StoreServices\CategoryService;
use App\Services\SettingsServices\CountryService;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    // YourController.php

    protected $productService;
    protected $checkToken;  // Set to true or false based on your need
    protected $lang;  // Set to true or false based on your need
    protected $brandService;
    protected $unitService;
    protected $categoryService;
    protected $countryService;



    public function __construct(ProductService $productService, BrandService $brandService, UnitService $unitService, CategoryService $categoryService, CountryService $countryService)
    {
        $this->productService = $productService;
        $this->brandService = $brandService;
        $this->unitService = $unitService;
        $this->countryService = $countryService;
        $this->categoryService = $categoryService;
        $this->checkToken = false;
    }

    public function index(Request $request)
    {

        // Pass it to the service
        $response  = $this->productService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $products = Product::hydrate($responseData['data']);

        return view('dashboard.product.list', compact('products'));
    }



    public function create(Request $request)
    {
        // Get active brands
        $Brands = Brand::where('is_active', 1)->get();

        // Fetch all brands via the brandService
        // $response = $this->brandService->index($request, $this->checkToken);
        // $responseData = json_decode($response->getContent(), true);
        // $Brands = Brand::hydrate($responseData['data']);

        // Fetch all units
        $response = $this->unitService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $Units = Unit::hydrate($responseData['data']);

        // Fetch all categories
        $response = $this->categoryService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        // $Categories = Category::hydrate($responseData['data']);
        $Categories = Category::where('active', 1)->get();

        // Fetch all countries
        $response = $this->countryService->producrIndex($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $Countries = Country::hydrate($responseData['data']);

        $Currencies = [];
        $seenCurrencies = []; // Track seen currency codes

        foreach ($Countries as $country) {
            if (isset($country->currency_code) && !in_array($country->currency_code, $seenCurrencies)) {
                $Currencies[] = ['id' => $country->id, 'code' => $country->currency_code];
                $seenCurrencies[] = $country->currency_code;
            }
        }

        // Return the view with the data
        return view('dashboard.product.add', compact('Brands', 'Categories', 'Units', 'Currencies'));
    }

    public function store(Request $request)
    {
        //        dd($request->all());
        $response = $this->productService->store($request, $this->checkToken);
        //        dd($response);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect('dashboard/products')->with('message', $message);
    }

    public function edit(Request $request, $id)
    {

        $product = Product::findOrFail($id);
        $product_limit = ProductStore::where('product_id', $product->id)->first();
        $product_unit = ProductUnit::where('product_id', $product->id)->first();

        // $response  = $this->brandService->index($request, $this->checkToken);
        // $responseData = json_decode($response->getContent(), true);
        // $Brands = Brand::hydrate($responseData['data']);
        $Brands = Brand::where('is_active', 1)->get();

        $response  = $this->unitService->index($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $Units = Unit::hydrate($responseData['data']);

        // $response  = $this->categoryService->index($request, $this->checkToken);
        // $responseData = json_decode($response->getContent(), true);
        // $Categories = Category::hydrate($responseData['data']);
        $Categories = Category::where('active', 1)->get();

        $response = $this->countryService->producrIndex($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);

        $Countries = Country::hydrate($responseData['data']);
        $Currencies = [];
        $seenCurrencies = [];

        foreach ($Countries as $country) {
            if (isset($country->currency_code) && !in_array($country->currency_code, $seenCurrencies)) {
                $Currencies[] = ['id' => $country->id, 'code' => $country->currency_code];
                $seenCurrencies[] = $country->currency_code;
            }
        }


        // $Stores = Store::all();

        return view('dashboard.product.edit', compact('product',  'Categories', 'Units', 'Currencies', 'Brands', 'product_limit', 'product_unit', 'id'));
    }

    public function show($id)
    {
        $request = new Request(); // Create a blank request object if needed

        $product = Product::with(['brand', 'productStore', 'images'])->findOrFail($id);

        $response = $this->countryService->producrIndex($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);

        $Countries = Country::hydrate($responseData['data']);
        $Currencies = [];

        // Prepare an associative array for $Currencies
        foreach ($Countries as $country) {
            if (isset($country->currency_code)) {
                $Currencies[$country->currency_code] = $country->currency_code; // Use country ID as the key and currency code as the value
            }
        }

        return view('dashboard.product.show', compact('product', 'Currencies', 'id'));
    }

    public function update(Request $request, $id)
    {
        $response = $this->productService->update($request, $id, $this->checkToken);
        // dd($response);
        //        dd($response);
        $responseData = $response->original;

        
    // Check if the response has a 'status' key
    if (isset($responseData['status']) && !$responseData['status']) {
        // Check if 'data' key exists and handle validation errors
        if (isset($responseData['data']) && is_array($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }

        // If 'data' key is not present, handle with the 'message' key
        if (isset($responseData['message'])) {
            return redirect()->back()->withErrors($responseData['message'])->withInput();
        }

        // Fallback for unexpected error formats
        return redirect()->back()->withErrors(__('Unexpected error occurred'))->withInput();
    }
        $message = $responseData['message'];
        return redirect('dashboard/products')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        //        dd($request->all());
        $response = $this->productService->delete($request, $id, $this->checkToken, true);
        //        dd($response);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/products')->with('message', $message);
    }

    public function unit(Request $request, $productId)
    {
        $response = $this->productService->list($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $product = Product::with('units')->findOrFail($productId); // Load product with units
        $units = Unit::whereNull('deleted_at')->get();  // Retrieve all sizes

        foreach ($product->units as $unit) {
            if ($unit->pivot) {
                $factor = $unit->pivot->factor ?? null;  // Safely access pivot data
                $unitId = $unit->pivot->unit_id ?? null;
            }
        }
        // dd($productId, $unitId);  // Debugging output

        return view('dashboard.product.unit.list', compact('product', 'units'));
    }

    public function saveUnits(Request $request, $productId)
    {
        // Call the service method to save the units
        $response = $this->productService->saveProductUnits($request, $productId);

        // Ensure the response is in the expected format
        $responseData = $response->original ?? [];

        // Check if the response has a 'status' key
        if (isset($responseData['status']) && !$responseData['status']) {
            // If 'data' key exists, handle validation errors
            if (isset($responseData['data'])) {
                $validationErrors = $responseData['data'];
                return redirect()->back()->withErrors($validationErrors)->withInput();
            } else {
                return redirect()->back()->withErrors($responseData['message'])->withInput();
            }

            // If no 'data' key is present, handle it gracefully
        }

        // Success message
        $message = $responseData['message'] ?? __('Operation completed successfully.');

        // Redirect with success message
        return redirect()->route('products.list', ['id' => $productId])->with('message', $message);
    }

    public function size(Request $request, $productId)
    {
        $response = $this->productService->listSize($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $product = Product::with('product_sizes')->findOrFail($productId); // Load product with sizes
        $sizes = Size::whereNull('deleted_at')->get();  // Retrieve all sizes

        foreach ($product->sizes as $size) {
            if ($size->pivot) {
                $code_size = $size->pivot->code_size ?? null;  // Safely access pivot data
                $sizeId = $size->pivot->size_id ?? null;
            }
        }
        // dd($productId, $unitId);  // Debugging output

        return view('dashboard.product.size.list', compact('product', 'sizes'));
    }

    public function saveSizes(Request $request, $productId)
    {
        // Call the service method to save the units
        $response = $this->productService->saveProductSizes($request, $productId);

        // Ensure the response is in the expected format
        $responseData = $response->original ?? [];

        // Check if the response has a 'status' key
        if (isset($responseData['status']) && !$responseData['status']) {
            // If 'data' key exists, handle validation errors
            if (isset($responseData['data'])) {
                $validationErrors = $responseData['data'];
                return redirect()->back()->withErrors($validationErrors)->withInput();
            } else {
                // dd(0);
                return redirect()->back()->withErrors($responseData['message'])->withInput();
            }

            // If no 'data' key is present, handle it gracefully
        }

        // Success message
        $message = $responseData['message'] ?? __('Operation completed successfully.');

        // Redirect with success message
        return redirect()->route('products.list', ['id' => $productId])->with('message', $message);
    }

    public function color(Request $request, $productId)
    {
        $response = $this->productService->listColor($request, $this->checkToken);
        $responseData = json_decode($response->getContent(), true);
        $product = Product::with('product_colors')->findOrFail($productId); // Load product with sizes
        $colors = Color::whereNull('deleted_at')->get();  // Retrieve all sizes

        foreach ($product->colors as $color) {
            if ($color->pivot) {
                $colorId = $color->pivot->color_id ?? null;
            }
        }
        // dd($productId, $unitId);  // Debugging output

        return view('dashboard.product.color.list', compact('product', 'colors'));
    }


    public function saveColors(Request $request, $productId)
    {
        // Call the service method to save the units
        $response = $this->productService->saveProductColors($request, $productId);

        // Ensure the response is in the expected format
        $responseData = $response->original ?? [];

        // Check if the response has a 'status' key
        if (isset($responseData['status']) && !$responseData['status']) {
            // If 'data' key exists, handle validation errors
            if (isset($responseData['data'])) {
                $validationErrors = $responseData['data'];
                return redirect()->back()->withErrors($validationErrors)->withInput();
            } else {
                // dd(0);
                return redirect()->back()->withErrors($responseData['message'])->withInput();
            }

            // If no 'data' key is present, handle it gracefully
        }

        // Success message
        $message = $responseData['message'] ?? __('Operation completed successfully.');

        // Redirect with success message
        return redirect()->route('products.list', ['id' => $productId])->with('message', $message);
    }
}
