<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\KitchenServices\RecipeService;
use Illuminate\Support\Facades\Log;

use Illuminate\Http\Request;

class RecipeController extends Controller
{
    protected $recipeService;
    protected $checkToken;  // Set to true or false based on your need

    public function __construct(RecipeService $recipeService)
    {
        $this->recipeService = $recipeService;
        $this->checkToken = false;
    }

    public function index()
    {
        $recipes = $this->recipeService->index()->get();
        return view('dashboard.recipes.index', compact('recipes'));
    }
    public function show($id)
    {
        try {
            $recipe = $this->recipeService->show($id);
            return view('dashboard.recipes.show', compact('recipe'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.recipes.index')->with('error', 'Failed to load recipe details.');
        }
    }

    public function create()
    {
        try {
            $products = $this->recipeService->getAllProducts();
            return view('dashboard.recipes.create', compact('products'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.recipes.index')->with('error', 'Failed to load the create recipe page.');
        }
    }

    public function store(Request $request)
    {
        try {
            // Log the incoming data
            Log::info('Storing a new recipe', ['data' => $request->all()]);
            // dd($request);
            // Validate the request
            $validatedData = $request->validate([
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'is_active' => 'required|boolean',
                'time' => 'nullable|integer',
                'ingredients' => 'required|array',
                'ingredients.*.product_id' => 'required|exists:products,id',
                'ingredients.*.quantity' => 'required|numeric|min:0',
                'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'item_code_id' => 'required|integer|exists:item_codes,id',
                'type' => 'required|integer|in:1,2',

            ]);

            // Call the service to store the recipe
            $this->recipeService->store($validatedData, $request->file('images'));

            // Redirect to index with success message
            return redirect()->route('dashboard.recipes.index')->with('success', 'Recipe created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors
            Log::error('Validation failed while creating recipe', ['errors' => $e->errors()]);

            // Redirect back with errors and input
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log unexpected exceptions
            Log::error('Failed to create recipe', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            // Redirect back with an error message
            return redirect()->back()->with('error', 'Failed to create recipe. Please try again later.');
        }
    }

    public function edit($id)
    {
        try {

            // Fetch the recipe along with its ingredients and other necessary data
            $recipe = $this->recipeService->show($id);

            // Fetch all products for ingredient selection
            $products = $this->recipeService->getAllProducts();

            return view('dashboard.recipes.edit', compact('recipe', 'products'));
        } catch (\Exception $e) {

            return redirect()->route('dashboard.recipes.index')->with('error', 'Failed to load recipe for editing.');
        }
    }

    public function update(Request $request, $id)
    {
        try {

            // Validate the request
            $validatedData = $request->validate([
                'name_ar' => 'required|string|max:255',
                'name_en' => 'required|string|max:255',
                'description_ar' => 'required|string',
                'description_en' => 'required|string',
                'is_active' => 'required|boolean',
                'ingredients' => 'required|array|min:1',
                'type' => 'required|integer|in:1,2',
                'time' => 'nullable|integer',
                'ingredients.*.product_id' => 'required|exists:products,id',
                'ingredients.*.quantity' => 'required|numeric|min:0',
                'ingredients.*.loss_percent' => 'nullable|numeric|min:0|max:100',
                'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                'item_code_id' => 'required|integer|exists:item_codes,id',
            ]);

            // Call the service to update the recipe
            $this->recipeService->update($id, $validatedData, $request->file('images'));

            return redirect()->route('dashboard.recipes.index')->with('success', 'Recipe updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log validation errors

            // Redirect back with errors and input
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            // Log unexpected exceptions

            // Redirect back with an error message
            return redirect()->back()->with('error', 'Failed to update recipe. Please try again later.');
        }
    }

    public function delete(Request $request, $id)
    {
        $response = $this->recipeService->delete($request, $id, $this->checkToken, true);
        $responseData = $response->original ?? [];
        $message = $responseData['message'] ?? __('recipes.An error occurred');

        if (isset($responseData['status']) && !$responseData['status']) {
            $validationErrors = $responseData['data'] ?? $message;
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }

        return redirect('dashboard/recipes')->with('message', $message);
    }

    public function restore($id)
    {
        try {
            $this->recipeService->restore($id);
            return redirect()->route('dashboard.recipes.index')->with('success', 'Recipe restored successfully.');
        } catch (\Exception $e) {
            return redirect()->route('dashboard.recipes.index')->with('error', 'Failed to restore recipe.');
        }
    }
}
