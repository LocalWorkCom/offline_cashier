<?php

namespace App\Http\Controllers\Dashboard;

use App\Events\DishStatus;
use App\Http\Controllers\Controller;
use App\Services\KitchenServices\DishService;
use App\Services\KitchenServices\DishCategoryService;
use App\Services\KitchenServices\AddonCategoryService;
use App\Services\KitchenServices\AddonService;
use App\Services\KitchenServices\CuisineService;
use App\Services\KitchenServices\RecipeService;
use App\Models\Dish;
use App\Models\Branch;
use App\Models\BranchMenuCategory;
use App\Models\DishIngredientStep;
use App\Models\MenusIntegration;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class DishController extends Controller
{
    protected $dishService;
    protected $dishCategoryService;
    protected $addonCategoryService;
    protected $addonService;
    protected $cuisineService;
    protected $recipeService;
    protected $checkToken;  // Set to true or false based on your need

    public function __construct(
        DishService $dishService,
        DishCategoryService $dishCategoryService,
        AddonCategoryService $addonCategoryService,
        AddonService $addonService,
        CuisineService $cuisineService,
        RecipeService $recipeService,

    ) {
        $this->dishService = $dishService;
        $this->dishCategoryService = $dishCategoryService;
        $this->addonCategoryService = $addonCategoryService;
        $this->addonService = $addonService;
        $this->cuisineService = $cuisineService;
        $this->recipeService = $recipeService;
        $this->checkToken = false;
    }
    public function index()
    {
        $dishes = $this->dishService->index()->get();
        return view('dashboard.Dish.index', compact('dishes'));
    }
    public function show($id)
    {
        try {
            $dish = $this->dishService->show($id);
            return view('dashboard.Dish.show', compact('dish'));
        } catch (\Exception $e) {
            return redirect()->route('dashboard.dishes.index')->with('error', __('dishes.DishNotFound'));
        }
    }
    public function create()
    {
        $categories = $this->dishCategoryService->index()->where('is_active', 1)->get();
        $addons = $this->addonService->index()->where('is_active', 1)->get();
        $addonCategories = $this->addonCategoryService->index()->get();
        $cuisines = $this->cuisineService->index(false)->where('is_active', 1)->get();
        $recipes = $this->recipeService->index()->where('is_active', 1)->where('type',1)->get();
        $branches = Branch::all();
        $applications = MenusIntegration::where('is_active', 1)->get();

        return view('dashboard.Dish.create', compact('categories', 'addons', 'addonCategories', 'cuisines', 'recipes', 'branches', 'applications'));
    }
    public function store(Request $request)
    {
        // dd($request);
        $response = $this->dishService->store($request->all(), $request->file('image'));
       if ($response instanceof \Illuminate\Http\JsonResponse) {
            // JsonResponse → نقدر نستخدم getData
            $responseData = $response->getData(true);
        } else{
            // Array → نشتغل بيه مباشرة
            $responseData = $response;
        }

        // Check if the response has a 'status' key
        if (isset($responseData['status']) && !$responseData['status']) {
            // Handle validation errors
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                return redirect()->back()->withErrors($responseData['data'])->withInput();
            }

            // Handle generic errors
            if (isset($responseData['message'])) {
                return redirect()->back()->with('error', $responseData['message'])->withInput();
            }

            // Fallback for unexpected error formats
            return redirect()->back()->with('error', __('Unexpected error occurred'))->withInput();
        }

        // Success case
        $message = $responseData['message'] ?? __('dishes.DishCreated');
        return redirect()->route('dashboard.dishes.index')->with('success', $message);
    }
    public function edit($id)
    {
        $dish = Dish::with([
            'sizes.recipes',
            'dishAddonsDetails.category', 
            'dishAddonsDetails.addons',
            'branchMenus.menusIntegrationDishs',
            'branchMenus.menusIntegrationDishSizes',
            'branchMenus.menusIntegrationDishAddons',
            ])->findOrFail($id);
        $categories = $this->dishCategoryService->index()->get();
        $cuisines = $this->cuisineService->index(false)->get();
        $recipes = $this->recipeService->index()->where('is_active', 1)->where('type',1)->get();
        $allAddons = $this->addonService->index()->get();
        $addonCategories = $this->addonCategoryService->index()->get();
        $addons = $dish->addons ?? collect();
        $applications = MenusIntegration::where('is_active', 1)->get();
        return view('dashboard.Dish.edit', compact('dish', 'categories', 'cuisines', 'recipes', 'allAddons', 'addonCategories', 'addons', 'applications'));
    }
    public function update(Request $request, $id)
    { 
        $response = $this->dishService->update($request->all(), $id);
        // Use getData(true) to ensure we get the response as an array
        // Check if the response is a JsonResponse or an array
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            // JsonResponse → نقدر نستخدم getData
            $responseData = $response->getData(true);
        } else{
            // Array → نشتغل بيه مباشرة
            $responseData = $response;
        }

        // Check if the response has a 'status' key
        if (isset($responseData['status']) && !$responseData['status']) {

            // Handle validation errors
            if (isset($responseData['data']) && is_array($responseData['data'])) {
                return redirect()->back()->withErrors($responseData['data'])->withInput();
            }

            // Handle generic errors
            if (isset($responseData['message'])) {
                return redirect()->back()->with('error', $responseData['message'])->withInput();
            }

            // Fallback for unexpected error formats
            return redirect()->back()->with('error', __('Unexpected error occurred'))->withInput();
        }


        // Success case
        $message = $responseData['message'] ?? __('dishes.DishUpdated');


        // Update branch menus if the dish was updated successfully
        $allBranchIds = Branch::whereHas('branchMenus', function ($query) use ($id) {
            return $query->where('dish_id', $id);
        })->pluck('id')->toArray();
        AddBranchesMenu($allBranchIds, $id, $menu_integrations=[]);

        foreach ($allBranchIds as $branch_id) {
            $branch_menu_category = BranchMenuCategory::where('branch_id', $branch_id)
                ->where('dish_category_id', $request->input('category_id'))
                ->first();
                DB::table('branch_menus')->where('dish_id', $id)->where('branch_id', $branch_id)->update([
                    'branch_menu_category_id' => $branch_menu_category->id
                ]);
        }

        return redirect()->route('dashboard.dishes.index')->with('success', $message);
    }

    public function destroy(Request $request, $id)
    {
        $response = $this->dishService->delete($id);
        $responseData = $response->original;
        $message = $responseData['message'];


        if (isset($responseData['status']) && !$responseData['status']) {
            // If 'data' key exists, handle validation errors
            if (isset($responseData['data'])) {
                $validationErrors = $responseData['data'];
                return redirect()->back()->withErrors($validationErrors)->withInput();
            } else {
                return redirect()->back()->withErrors($responseData['message'])->withInput();
            }
        }

        return redirect('dashboard/dishes')->with('message', $message);
    }



    public function restore($id)
    {
        $this->dishService->restore($id);
        return redirect()->route('dashboard.dish.index')->with('success', 'Dish restored successfully.');
    }
    public function showIngredientForm($dishId)
    {
        $dishIngredients = DishIngredientStep::where('dish_id', $dishId)->get();

        $groupedData = [
            'groups' => [],
            'notes' => []
        ];

        foreach ($dishIngredients as $ingredient) {
            // Handle recipe data - convert text back to array for the view
            if ($ingredient->recipe_title && $ingredient->recipe_steps) {
                $groupedData['groups'][] = [
                    'title' => $ingredient->recipe_title,
                    'recipes' => explode("\n", $ingredient->recipe_steps), // Convert text to array using line breaks
                ];
            }

            // Handle note data - convert text back to array for the view
            if ($ingredient->note_title && $ingredient->note_steps) {
                $groupedData['notes'][] = [
                    'title' => $ingredient->note_title,
                    'notes' => explode("\n", $ingredient->note_steps), // Convert text to array using line breaks
                ];
            }
        }

        return view('dashboard.Dish.ingredient', compact('dishId', 'groupedData'));
    }

    public function saveIngredient(Request $request)
    {
        $response = $this->dishService->saveIngredient($request);

        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getData(true);
        } elseif (is_object($response) && isset($response->original)) {
            $responseData = $response->original;
        } else {
            $responseData = $response;
        }

        if (isset($responseData['status']) && !$responseData['status']) {
            if (isset($responseData['data'])) {
                $validationErrors = $responseData['data'];
                return redirect()->back()->withErrors($validationErrors)->withInput();
            } else {
                $errorMessage = $responseData['message'] ?? 'An error occurred';
                return redirect()->back()->with('error', $errorMessage)->withInput();
            }
        }

        $message = $responseData['message'] ?? 'Ingredients saved successfully';
        return redirect()->route('dashboard.dishes.index')->with('success', $message);
    }
}
