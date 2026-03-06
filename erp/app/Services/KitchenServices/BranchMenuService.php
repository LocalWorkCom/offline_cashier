<?php

namespace App\Services\KitchenServices;

use App\Events\DishStatus;
use App\Models\BranchMenu;
use App\Models\MenusIntegration;
use App\Models\MenusIntegrationDish;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchMenuService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        try {
            $query = BranchMenu::with(['branches', 'branchMenuCategories', 'dish']);

            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->where('branch_id', $branch_id);
                }
            }
            $branch_menu_categories = $query->get();
            return ResponseWithSuccessData($this->lang, $branch_menu_categories, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching branches: ' . $e->getMessage());
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function branch($id)
    {
        try {
            return BranchMenu::where('branch_id', $id)
                ->with(['branches', 'branchMenuCategories.dish_categories', 'dish']);
        } catch (\Exception $e) {
            Log::error('Error fetching branches: ' . $e->getMessage());
            return collect(); // Return empty collection on error
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $branch_menu = BranchMenu::with(['branches', 'branchMenuCategories.dish_categories', 'dish', 'menusIntegrationDishs.menusIntegrations'])->findOrFail($id);
        return ResponseWithSuccessData($this->lang, $branch_menu, 1);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $input = $request->all();

        // Validation
        $validator = Validator::make($input, [
            'price' => 'required|numeric',
            'is_active' => 'required|integer',
            'menus_integration_dishs' => 'nullable|array',
            'menus_integration_dishs.*.id' => 'nullable|integer|exists:menus_integration_dishes,id',
            'menus_integration_dishs.*.menus_integration_id' => 'required|integer|exists:menus_integrations,id',
            'menus_integration_dishs.*.price' => 'required|numeric',
            'menus_integration_dishs.*.is_taxed' => 'required|boolean',
            'menus_integration_dishs.*.is_percentage' => 'required|boolean',
            'menus_integration_dishs.*.percentage_amount' => 'nullable|numeric',
        ]);

        if (!empty($input['menus_integration_dishs']) && is_array($input['menus_integration_dishs'])) {
            foreach ($input['menus_integration_dishs'] as $index => $dish) {
                if (isset($dish['is_percentage']) && $dish['is_percentage'] == 1) {
                    $validator->addRules([
                        "menus_integration_dishs.$index.percentage_amount" => 'required|numeric',
                    ]);
                }
            }
        }

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors(), 'input' => $input]);
            return RespondWithBadRequestWithData($validator->errors());
        }

        $branch_menu_category = BranchMenu::find($id);
        if (!$branch_menu_category) {
            return RespondWithBadRequestNotExist();
        }

        $branch_menu_category->price = $request->price;
        $branch_menu_category->is_active = $request->is_active;
        $branch_menu_category->modified_by = authActionSave()['by'];
        $branch_menu_category->modified_by_type = authActionSave()['type'];
        $branch_menu_category->save();

        //

        if ($request->filled('menus_integration_dishs') && count($request->menus_integration_dishs) > 0) {
            $menusIntegrationIds = [];
            foreach ($request->menus_integration_dishs as $menu_dish) {
                if (empty($menu_dish['menus_integration_id']) && empty($menu_dish['id'])) {
                    continue;
                }

                $data = [
                    'price' => $menu_dish['price'] ?? 0,
                    'is_taxed' => $menu_dish['is_taxed'] ?? 0,
                    'is_percentage' => $menu_dish['is_percentage'] ?? 0,
                    'percentage_amount' => $menu_dish['percentage_amount'] ?? null
                ];

                if (!empty($menu_dish['menus_integration_id'])) {
                    $menusIntegrationIds[] = $menu_dish['menus_integration_id'];
                }

                if (!empty($menu_dish['id'])) {
                    // Update existing integration
                    $edit_menu_dish = MenusIntegrationDish::find($menu_dish['id']);
                    if ($edit_menu_dish) {
                        $data['modified_by'] = authActionSave()['by'];
                        $data['modified_by_type'] = authActionSave()['type'];
                        $edit_menu_dish->update($data);
                    }
                } else {
                    $edit_menu_dish = MenusIntegrationDish::where('branch_menu_id', $id)->where('menus_integration_id', $menu_dish['menus_integration_id'])->first();
                    if ($edit_menu_dish) {
                        // return RespondWithBadRequestWithData(__('branch_menu.alraedy_added'));
                        return respondError(__('branch_menu.alraedy_added'), 404);
                    } else {
                        //Create new integration if doesn't exist
                        $data['menus_integration_id'] = $menu_dish['menus_integration_id'];
                        // $data['branch_menu_id'] = $menu_dish['branch_menu_id'];
                        $data['branch_menu_id'] = $id;
                        $data['created_by'] = authActionSave()['by'];
                        $data['created_by_type'] = authActionSave()['type'];
                        MenusIntegrationDish::create($data);
                    }
                }
            }

            if (!empty($menusIntegrationIds)) {
                if ($branch_menu_category) {
                    $branch_menu_category->menus_integration_ids = json_encode(array_unique($menusIntegrationIds));
                    $branch_menu_category->is_menus_integration = 1;
                    $branch_menu_category->save();
                }
            }

            if (!empty($menusIntegrationIds)) {
                if ($branch_menu_category) {
                    $branch_menu_category->menus_integration_ids = json_encode(array_unique($menusIntegrationIds));
                    $branch_menu_category->is_menus_integration = 1;
                    $branch_menu_category->save();
                }
            }
        }



        broadcast(new DishStatus($branch_menu_category, $branch_menu_category->branch_id, $branch_menu_category->is_active));
        return ResponseWithSuccessData($this->lang, $branch_menu_category, 1);
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function change_status($id)
    {
        // $user_id =  Auth::guard('admin')->user()->id;
        $active = 1;
        $branch_menu = BranchMenu::findOrFail($id);
        if ($branch_menu->is_active == 1) {
            $active = 0;
        }
        $branch_menu->is_active = $active;
        $branch_menu->modified_by = authActionSave()['by'];
        $branch_menu->modified_by_type = authActionSave()['type'];
        $branch_menu->save();
        return ResponseWithSuccessData($this->lang, $branch_menu, 1);
    }
}
