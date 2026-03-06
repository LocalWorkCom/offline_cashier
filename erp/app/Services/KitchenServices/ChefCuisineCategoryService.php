<?php

namespace App\Services\KitchenServices;

use App\Models\Dish;
use App\Models\Employee;
use App\Models\CuisineCategory;
use App\Models\ChefCuisineCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChefCuisineCategoryService
{
    public function index()
    {
        try {
            // Start with base queries
            $chefQuery = Employee::where('flag', 'chef');
            $assignedQuery = ChefCuisineCategory::with(['chef', 'chef.branch', 'cuisineCategory.dish_category', 'cuisineCategory.cuisine'])
                ->whereHas('chef'); // This ensures only records with existing chefs are returned

            // Apply branch filter for Branch Managers
            if (auth('admin')->user() && auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    // Filter chefs by branch
                    $chefQuery->whereHas('branch', function ($q) use ($branch_id) {
                        $q->where('id', $branch_id);
                    });

                    // Filter assigned cuisines by chef's branch
                    $assignedQuery->whereHas('chef.branch', function ($q) use ($branch_id) {
                        $q->where('id', $branch_id);
                    });
                }
            }else if (auth('employee')->user() &&auth('employee')->user()->hasRole('Branch_Manager')) {
                // If the user is a chef, only show their own assignments
                $chefId = auth('employee')->id();
                $chefQuery->where('id', $chefId);
                $assignedQuery->where('employee_id', $chefId);
            }

            // Execute queries
            $assignedCuisines = $assignedQuery;
            $chefs = $chefQuery->get();
            $cuisineCategories = CuisineCategory::all();

            return [
                'success' => true,
                'data' => [
                    'assignedCuisines' => $assignedCuisines,
                    'chefs' => $chefs,
                    'cuisineCategories' => $cuisineCategories
                ]
            ];
        } catch (\Exception $e) {
            Log::error("Error in ChefCuisineCategoryService@index: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch data'
            ];
        }
    }


    public function create()
    {
        $chefs = Employee::where('flag', 'chef')->get();
        $cuisineCategories = CuisineCategory::all();

        return [
            'chefs' => $chefs,
            'cuisineCategories' => $cuisineCategories
        ];
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'categories' => 'required|array',
            'categories.*.category_id' => 'required|exists:cuisines_categories,id',
            'categories.*.dishes' => 'nullable|array',
            'categories.*.dishes.*' => [
                function ($attribute, $value, $fail) {
                    if ($value != -1 && !Dish::where('id', $value)->exists()) {
                        $fail(__('validation.The selected dish is invalid.'));
                    }
                },
            ],
        ]);

        $createdAssignments = [];
        $failedAssignments = [];

        foreach ($request->categories as $categoryData) {
            $chefId = $request->employee_id;
            $cuisineCategoryId = $categoryData['category_id'];
            $dishes = $categoryData['dishes'] ?? [];

            // Convert all dish IDs to integers
            $dishes = array_map('intval', $dishes);

            if (empty($dishes) || in_array(-1, $dishes)) {
                $cuisineCategory = CuisineCategory::find($cuisineCategoryId);
                $dishes = Dish::where('category_id', $cuisineCategory->dish_category_id)
                    ->where('cuisine_id', $cuisineCategory->cuisine_id)
                    ->pluck('id')
                    ->map(function ($id) {
                        return (int)$id;
                    })
                    ->toArray();
                $dishesToStore = [-1];
            } else {
                $dishesToStore = $dishes;
            }

            $existing = ChefCuisineCategory::where('employee_id', $chefId)
                ->where('cuisine_category_id', $cuisineCategoryId)
                ->whereJsonContains('dishes', $dishesToStore)
                ->first();

            if ($existing) {
                $cuisineCategoryName = $this->getCuisineCategoryName($cuisineCategoryId);
                $failedAssignments[] = $cuisineCategoryName;
            } else {
                $overlap = ChefCuisineCategory::where('employee_id', $chefId)
                    ->where('cuisine_category_id', $cuisineCategoryId)
                    ->first();

                if ($overlap) {
                    $cuisineCategoryName = $this->getCuisineCategoryName($cuisineCategoryId);
                    $failedAssignments[] = $cuisineCategoryName . ' (' . __('validation.An assignment for this cuisine category already exists for this chef.') . ')';
                } else {
                    $assignment = ChefCuisineCategory::create([
                        'employee_id' => $chefId,
                        'cuisine_category_id' => $cuisineCategoryId,
                        'dishes' => $dishesToStore,
                        'created_by' => Auth::id(),
                    ]);
                    $createdAssignments[] = $assignment;
                }
            }
        }

        // Rest of the method remains the same...
        if (empty($createdAssignments) && !empty($failedAssignments)) {
            return [
                'success' => false,
                'message' => __('validation.no_new_assignments_found_issues') . "\n- " . implode("\n- ", array_unique($failedAssignments))
            ];
        } elseif (!empty($createdAssignments) && !empty($failedAssignments)) {
            return [
                'success' => true,
                'message' => __('validation.assigned_successfully') . "\n" . __('validation.Some assignments were skipped due to:') . "\n- " . implode("\n- ", array_unique($failedAssignments))
            ];
        } elseif (empty($createdAssignments) && empty($failedAssignments)) {
            return [
                'success' => false,
                'message' => __('validation.No categories were selected for assignment.')
            ];
        }

        return [
            'success' => true,
            'message' => __('validation.assigned_successfully')
        ];
    }
    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'employee_id' => 'required|exists:employees,id',
    //         'categories' => 'required|array',
    //         'categories.*.category_id' => 'required|exists:cuisines_categories,id',
    //         'categories.*.dishes' => 'nullable|array',
    //         'categories.*.dishes.*' => [
    //             function ($attribute, $value, $fail) {
    //                 if ($value != -1 && !Dish::where('id', $value)->exists()) {
    //                     $fail(__('validation.The selected dish is invalid.'));
    //                 }
    //             },
    //         ],
    //     ]);

    //     $createdAssignments = [];
    //     $failedAssignments = [];

    //     foreach ($request->categories as $categoryData) {
    //         $chefId = $request->employee_id;
    //         $cuisineCategoryId = $categoryData['category_id'];
    //         $dishes = $categoryData['dishes'] ?? [];

    //         // Convert all dish IDs to integers
    //         $dishes = array_map('intval', $dishes);

    //         if (empty($dishes) || in_array(-1, $dishes)) {
    //             $cuisineCategory = CuisineCategory::find($cuisineCategoryId);
    //             $dishes = Dish::where('category_id', $cuisineCategory->dish_category_id)
    //                 ->where('cuisine_id', $cuisineCategory->cuisine_id)
    //                 ->pluck('id')
    //                 ->map(function ($id) {
    //                     return (int)$id;
    //                 })
    //                 ->toArray();
    //             $dishesToStore = [-1];
    //         } else {
    //             $dishesToStore = $dishes;
    //         }

    //         $existing = ChefCuisineCategory::where('employee_id', $chefId)
    //             ->where('cuisine_category_id', $cuisineCategoryId)
    //             ->whereJsonContains('dishes', $dishesToStore)
    //             ->first();

    //         if ($existing) {
    //             $cuisineCategoryName = $this->getCuisineCategoryName($cuisineCategoryId);
    //             $failedAssignments[] = $cuisineCategoryName;
    //         } else {
    //             $overlap = ChefCuisineCategory::where('employee_id', $chefId)
    //                 ->where('cuisine_category_id', $cuisineCategoryId)
    //                 ->first();

    //             if ($overlap) {
    //                 $cuisineCategoryName = $this->getCuisineCategoryName($cuisineCategoryId);
    //                 $failedAssignments[] = $cuisineCategoryName . ' (' . __('validation.An assignment for this cuisine category already exists for this chef.') . ')';
    //             } else {
    //                 $assignment = ChefCuisineCategory::create([
    //                     'employee_id' => $chefId,
    //                     'cuisine_category_id' => $cuisineCategoryId,
    //                     'dishes' => $dishesToStore,
    //                     'created_by' => Auth::id(),
    //                 ]);
    //                 $createdAssignments[] = $assignment;
    //             }
    //         }
    //     }

    //     // Rest of the method remains the same...
    //     if (empty($createdAssignments) && !empty($failedAssignments)) {
    //         return [
    //             'success' => false,
    //             'message' => __('validation.no_new_assignments_found_issues') . "\n- " . implode("\n- ", array_unique($failedAssignments))
    //         ];
    //     } elseif (!empty($createdAssignments) && !empty($failedAssignments)) {
    //         return [
    //             'success' => true,
    //             'message' => __('validation.assigned_successfully') . "\n" . __('validation.Some assignments were skipped due to:') . "\n- " . implode("\n- ", array_unique($failedAssignments))
    //         ];
    //     } elseif (empty($createdAssignments) && empty($failedAssignments)) {
    //         return [
    //             'success' => false,
    //             'message' => __('validation.No categories were selected for assignment.')
    //         ];
    //     }

    //     return [
    //         'success' => true,
    //         'message' => __('validation.assigned_successfully')
    //     ];
    // }
    public function getDishesByCuisineCategory($cuisineCategoryId)
    {
        try {
            $cuisineCategory = CuisineCategory::with(['dish_category', 'cuisine'])
                ->find($cuisineCategoryId);

            $dishes = Dish::where('category_id', $cuisineCategory->dish_category_id)
                ->where('cuisine_id', $cuisineCategory->cuisine_id)
                ->get()
                ->map(function ($dish) {
                    return [
                        'id' => $dish->id,
                        'name_en' => $dish->name_en,
                        'name_ar' => $dish->name_ar,
                    ];
                });

            return $dishes;
        } catch (\Exception $e) {
            Log::error("Error in getDishesByCuisineCategory: " . $e->getMessage());
            return [
                'error' => 'Error fetching dishes',
                'details' => $e->getMessage()
            ];
        }
    }

    public function getAvailableCuisines($chefId)
    {
        $assignedCategories = ChefCuisineCategory::where('employee_id', $chefId)
            ->pluck('cuisine_category_id')
            ->toArray();

        $availableCategories = CuisineCategory::whereNotIn('id', $assignedCategories)
            ->get(['id', 'name_en', 'name_ar']);

        return $availableCategories;
    }

    public function destroy($id)
    {
        try {
            $assignment = ChefCuisineCategory::findOrFail($id);
            $assignment->delete();

            return [
                'success' => true,
                'message' => 'Cuisine unassigned successfully'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    public function clearAssignments($id)
    {
        // try {
        $assignment = ChefCuisineCategory::where('employee_id', $id)->get();
        $assignment->each->delete();

        return [
            'success' => true,
            'message' => 'Cuisine unassigned successfully'
        ];
        // } catch (\Exception $e) {
        //     return [
        //         'success' => false,
        //         'message' => 'Error: ' . $e->getMessage()
        //     ];
        // }
    }

    private function getCuisineCategoryName($cuisineCategoryId)
    {
        $cuisineCategory = CuisineCategory::find($cuisineCategoryId);
        return app()->getLocale() == 'en'
            ? $cuisineCategory->dish_category->name_en . ' - ' . $cuisineCategory->cuisine->name_en
            : $cuisineCategory->dish_category->name_ar . ' - ' . $cuisineCategory->cuisine->name_ar;
    }
}
