<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\CuisineCategory;
use App\Http\Controllers\Controller;
use App\Services\KitchenServices\ChefCuisineCategoryService;

class ChefCuisineCategoryController extends Controller
{
    protected $chefCuisineService;

    public function __construct(ChefCuisineCategoryService $chefCuisineService)
    {
        $this->chefCuisineService = $chefCuisineService;
    }

    public function index()
    {
        $response = $this->chefCuisineService->index();
        
        if (!$response['success']) {
            return back()->with('error', $response['message']);
        }

        $data = $response['data'];
        return view('dashboard.chef_cuisine.index', [
            'assignedCuisines' => $data['assignedCuisines']->get(),
            'chefs' => $data['chefs'],
            'cuisineCategories' => $data['cuisineCategories']
        ]);
    }

    public function create()
    {
        $chefs = Employee::where('flag', 'chef')->get();
        $cuisineCategories = CuisineCategory::all();

        return view('dashboard.chef_cuisine.create', compact('chefs', 'cuisineCategories'));
    }

    public function store(Request $request)
    {
        $response = $this->chefCuisineService->store($request);
        
        if ($response['success']) {
            return response()->json($response);
        }
        
        return response()->json($response, 400);
    }

    public function getDishesByCuisineCategory($cuisineCategoryId)
    {
        $dishes = $this->chefCuisineService->getDishesByCuisineCategory($cuisineCategoryId);
        return response()->json($dishes);
    }

    public function getAvailableCuisines($chefId)
    {
        $cuisines = $this->chefCuisineService->getAvailableCuisines($chefId);
        return response()->json($cuisines);
    }

    public function destroy($id)
    {
        $response = $this->chefCuisineService->destroy($id);
        
        if ($response['success']) {
            return response()->json($response);
        }
        
        return response()->json($response, 500);
    }
}