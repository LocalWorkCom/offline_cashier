<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\KitchenServices\DishCategoryService;
use Illuminate\Http\Request;

class DishCategoryController extends Controller
{
    protected $dishCategoryService;

    public function __construct(DishCategoryService $dishCategoryService)
    {
        $this->dishCategoryService = $dishCategoryService;
    }

    public function index()
    {
        $categories = $this->dishCategoryService->index()->get();

        return view('dashboard.dish_categories.index', compact('categories'));
    }
    public function show($id)
    {
        try {
            $category = $this->dishCategoryService->show($id);

            return view('dashboard.dish_categories.show', compact('category'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.dish-categories.index')->with('error', 'Failed to load the category details.');
        }
    }

    public function create()
    {
        try {
            // Fetch parent categories excluding active ones
            $categories = $this->dishCategoryService->index()->get();
            return view('dashboard.dish_categories.create', compact('categories'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.dish-categories.index')->with('error', 'Failed to load the create page.');
        }
    }


    public function store(Request $request)
    {
        try {
            $data = $request;
            $data['created_by'] = auth('admin')->id();

            $this->dishCategoryService->store($data);

            return redirect()->route('dashboard.dish-categories.index')
                ->with('success', __('dishes.Dish category created successfully.'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An error occurred while creating the dish category.');
        }
    }


    public function edit($id)
    {
        try {
            // Fetch the category to be edited
            $category = $this->dishCategoryService->show($id);

            // Fetch all active categories except the current one
            $categories = $this->dishCategoryService->index()->get();

            return view('dashboard.dish_categories.edit', compact('category', 'categories'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.dish-categories.index')->with('error', 'Failed to load the edit page.');
        }
    }


    public function update(Request $request, $id)
    {
        try {
            $data = $request;
            $data['modified_by'] = auth('admin')->id();

            $this->dishCategoryService->update($id, $data);

            return redirect()->route('dashboard.dish-categories.index')
                ->with('success', __('dishes.dish_category_updated'));
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('dishes.error_updating'));
        }
    }

    public function delete($id)
    {
        try {
            // Assuming the service layer handles the logic
            $response = $this->dishCategoryService->delete($id);
            $responseData = $response->original;

            // Check if the response indicates success
            if ($responseData['status']) {
                return response()->json(['message' => $responseData['message']], 200);
            }

            // Handle cases where deletion failed (e.g., child categories exist)
            return response()->json(['message' => $responseData['message']], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }




    public function restore($id)
    {
        try {
            $this->dishCategoryService->restore($id);

            return redirect()->route('dashboard.dish-categories.index')->with('success', 'Dish category restored successfully.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.dish-categories.index')->with('error', 'An error occurred while restoring the dish category.');
        }
    }
}
