<?php

namespace App\Http\Controllers\Api\DashboardAPIs\KitchenSettingsAPIs;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Menu;
use App\Models\Dish;
use Illuminate\Support\Facades\Log;

class MenuController extends Controller
{
    public function index(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $menus = Menu::with(['branch', 'dish'])
                ->get()
                ->groupBy('branch_id')
                ->map(function ($branchMenus) {
                    return [
                        'branch_id' => $branchMenus->first()->branch_id,
                        'branch_name' => $branchMenus->first()->branch->name,
                        'dishes' => $branchMenus->map(function ($menu) {
                            return [
                                'dish_id' => $menu->dish_id,
                                'dish_name' => $menu->dish->name,
                                'price' => $menu->price,
                            ];
                        })->values()
                    ];
                })->values();

            return ResponseWithSuccessData($lang, $menus, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching menus: ' . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function show(Request $request, $branch_id)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $menus = Menu::with(['branch', 'dish'])
                ->where('branch_id', $branch_id)
                ->get();

            if ($menus->isEmpty()) {
                return ResponseWithSuccessData($lang, [], 1);
            }

            $branch = $menus->first()->branch;

            $response = [
                'branch_id' => $branch->id,
                'branch_name' => $branch->name,
                'dishes' => $menus->map(function ($menu) {
                    return [
                        'dish_id' => $menu->dish_id,
                        'dish_name' => $menu->dish->name,
                        'price' => $menu->price,
                    ];
                })->values()
            ];

            return ResponseWithSuccessData($lang, $response, 1);
        } catch (\Exception $e) {
            Log::error("Error fetching menu for branch $branch_id: " . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function store(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $request->validate([
                'branch_id' => 'required|integer|exists:branches,id',
            ]);

            $branch_id = $request->branch_id;

            if (Menu::where('branch_id', $branch_id)->exists()) {
                return RespondWithBadRequestData($lang, 'This branch already has menu items added.');
            }

            $dishes = Dish::all();

            if ($dishes->isEmpty()) {
                return RespondWithBadRequestData($lang, 'No dishes available yet.');
            }

            $newMenus = [];
            foreach ($dishes as $dish) {
                $newMenus[] = [
                    'branch_id' => $branch_id,
                    'dish_id' => $dish->id,
                    'price' => $dish->price,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Menu::insert($newMenus);

            return ResponseWithSuccessData($lang, 'Menu items added successfully for this branch.', 1);
        } catch (\Exception $e) {
            Log::error("Error adding menu items for branch $branch_id: " . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function update(Request $request, $branch_id)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $request->validate([
                'dishes' => 'required|array',
                'dishes.*.dish_id' => 'required|integer|exists:dishes,id',
                'dishes.*.price' => 'required|numeric|min:0',
            ]);

            $dishesData = $request->dishes;


            if (!Menu::where('branch_id', $branch_id)->exists()) {
                return RespondWithBadRequestData($lang, 'No menu items exist for this branch.');
            }

            // Update prices for each dish
            foreach ($dishesData as $dishData) {
                Menu::where('branch_id', $branch_id)
                    ->where('dish_id', $dishData['dish_id'])
                    ->update([
                        'price' => $dishData['price'],
                        'modified_by' => auth()->id(),
                    ]);
            }

            return ResponseWithSuccessData($lang, 'Menu prices updated successfully for this branch.', 1);
        } catch (\Exception $e) {
            Log::error("Error updating menu prices for branch $branch_id: " . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }


    public function destroy(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $request->validate([
                'branch_id' => 'required|integer|exists:branches,id',
                'dish_id' => 'required|integer|exists:dishes,id',
            ]);

            $branch_id = $request->branch_id;
            $dish_id = $request->dish_id;

            $menu = Menu::where('branch_id', $branch_id)
                        ->where('dish_id', $dish_id)
                        ->first();

            if (!$menu) {
                return RespondWithBadRequestData($lang, 'Menu item not found for the specified branch and dish.');
            }

            $menu->deleted_by = auth()->id();
            $menu->save();
            $menu->delete();

            return ResponseWithSuccessData($lang, 'Menu item deleted successfully.', 1);
        } catch (\Exception $e) {
            Log::error("Error deleting menu item for branch $branch_id, dish $dish_id: " . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }

    public function restore(Request $request)
    {
        try {
            $lang = $request->header('lang', 'ar');

            $request->validate([
                'branch_id' => 'required|integer|exists:branches,id',
                'dish_id' => 'required|integer|exists:dishes,id',
            ]);

            $branch_id = $request->branch_id;
            $dish_id = $request->dish_id;

            $menu = Menu::onlyTrashed()
                        ->where('branch_id', $branch_id)
                        ->where('dish_id', $dish_id)
                        ->first();

            if (!$menu) {
                return RespondWithBadRequestData($lang, 'No deleted menu item found for the specified branch and dish.');
            }

            $menu->restore();

            return ResponseWithSuccessData($lang, 'Menu item restored successfully.', 1);
        } catch (\Exception $e) {
            Log::error("Error restoring menu item for branch $branch_id, dish $dish_id: " . $e->getMessage());
            return RespondWithBadRequestData($lang, 2);
        }
    }
}
