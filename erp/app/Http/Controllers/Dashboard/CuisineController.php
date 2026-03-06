<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Cuisine;
use App\Models\DishCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CuisineCategory;
use Illuminate\Support\Facades\Auth;
use App\Services\KitchenServices\CuisineService;

class CuisineController extends Controller
{
    protected $cuisineService;
    protected $checkToken;  // Set to true or false based on your need

    public function __construct(CuisineService $cuisineService)
    {
        $this->cuisineService = $cuisineService;
        $this->checkToken = false;
    }
public function getDishesForCategory($id)
{
    $category = CuisineCategory::with('dishes')->findOrFail($id);

    return response()->json($category->dishes);
}

    public function index()
    {
        $cuisines = Cuisine::all();
        $dishCategories = DishCategory::where('deleted_at', null)
            ->where('is_active', 1)
            ->get();
        return view('dashboard.cuisines.index', compact('cuisines', 'dishCategories'));
    }

    public function show($id)
    {
        $cuisine = $this->cuisineService->show($id);
        return view('dashboard.cuisines.show', compact('cuisine'));
    }

    public function create()
    {
        return view('dashboard.cuisines.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'required|string|max:255',
            'description_ar' => 'required|string',
            'description_en' => 'required|string',
            'is_active' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpg,png,jpeg',
        ]);

        $this->cuisineService->store($data, $request->file('image'));

        return redirect()->route('dashboard.cuisines.index')->with('success', __('messages.cuisine_created'));
    }

    public function edit($id)
    {
        $cuisine = $this->cuisineService->show($id);
        return view('dashboard.cuisines.edit', compact('cuisine'));
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:255',
            'name_en' => 'nullable|string|max:255',
            'description_ar' => 'nullable|string',
            'description_en' => 'nullable|string',
            'is_active' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpg,png,jpeg',
        ]);

        $this->cuisineService->update($id, $data, $request->file('image'));

        return redirect()->route('dashboard.cuisines.index')->with('success', __('messages.cuisine_updated'));
    }

    // public function destroy($id)
    // {
    //     $this->cuisineService->delete($id);
    //     return redirect()->route('dashboard.cuisines.index')->with('success', 'Cuisine deleted successfully.');
    // }

    public function destroy(Request $request, $id)
    {
        $response = $this->cuisineService->delete( $id);
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

        return redirect('dashboard/cuisines')->with('message', $message);
    }


    public function restore($id)
    {
        $this->cuisineService->restore($id);
        return redirect()->route('dashboard.cuisines.index')->with('success', __('messages.cuisine_restored'));
    }

    public function assignDishCategories(Request $request, $id)
    {
        $cuisine = Cuisine::findOrFail($id);
        $userId = auth('admin')->id();

        // Get the currently assigned dish categories
        $currentCategories = $cuisine->dishCategories()->pluck('dish_category_id')->toArray();

        // Get the requested categories
        $requestedCategories = $request->input('dish_categories', []);
        // Find categories to be removed
        $categoriesToRemove = array_diff($currentCategories, $requestedCategories);

        // Check if any of the categories to be removed are still in use
        $usedCategories = DB::table('chef_cuisine_categories')
            ->whereIn('cuisine_category_id', function ($query) use ($categoriesToRemove, $id) {
                $query->select('id')
                    ->from('cuisines_categories')
                    ->where('cuisine_id', $id)
                    ->whereIn('dish_category_id', $categoriesToRemove);
            })
            ->whereNull('deleted_at')
            ->pluck('cuisine_category_id')
            ->toArray();

        if (!empty($usedCategories)) {
            // Get the dish category names for the error message
            $usedDishCategories = DB::table('dish_categories')
                ->whereIn('id', function ($query) use ($usedCategories, $id) {
                    $query->select('dish_category_id')
                        ->from('cuisines_categories')
                        ->where('cuisine_id', $id)
                        ->whereIn('id', $usedCategories);
                })
                ->pluck(app()->getLocale() == 'en' ? 'name_en' : 'name_ar')
                ->toArray();

            return back()->withErrors([
                'dish_categories' => __('validation.this category are still assigned to chefs and cannot be removed')
            ]);
        }

        // Proceed with the sync if no conflicts
        $syncData = [];
        foreach ($requestedCategories as $dishCategoryId) {
            $syncData[$dishCategoryId] = [
                'created_by' => $userId,
                'modified_by' => $userId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        $cuisine->dishCategories()->sync($syncData);

        return redirect()->route('dashboard.cuisines.index')
            ->with('message', __('validation.Dish categories updated successfully.'));
    }
}
