<?php

namespace App\Services\KitchenServices;

use App\Models\BranchMenuAddon;
use App\Models\MenusIntegrationDishAddon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchMenuAddonService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }

    public function index(Request $request)
    {
        // try {

            $query = BranchMenuAddon::with(['branches', 'dishes', 'dishAddons.addons', 'branchMenuAddonCategories.addonCategories', 'menusIntegrationDishAddons.menusIntegrations']);
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->where('branch_id', $branch_id);
                }
            }
            $branch_menu_addons = $query->get();
            return ResponseWithSuccessData($this->lang, $branch_menu_addons, 1);
        // } catch (\Exception $e) {
        //     Log::error('Error fetching branches: ' . $e->getMessage());
        //     return RespondWithBadRequestData($this->lang, 2);
        // }
    }

    public function branch($id, $asQuery = false)
    {
        $branch_menu_addons = BranchMenuAddon::where('branch_id', $id)
            ->with(['branches', 'dishes', 'dishAddons.addons', 'branchMenuAddonCategories.addonCategories']);

        if ($asQuery) {
            return $branch_menu_addons;
        }

        return ResponseWithSuccessData($this->lang, $branch_menu_addons, 1);
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
        $branch_menu_addon = BranchMenuAddon::with(['branches', 'dishes', 'dishAddons.addons', 'branchMenuAddonCategories.addonCategories', 'menusIntegrationDishAddons.menusIntegrations'])->findOrFail($id);
        return ResponseWithSuccessData($this->lang, $branch_menu_addon, 1);
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
            'menus_integration_dish_addons' => 'nullable|array',
            'menus_integration_dish_addons.*.id' => 'nullable|integer|exists:menus_integration_dish_addons,id',
            'menus_integration_dish_addons.*.menus_integration_id' => 'required|integer|exists:menus_integrations,id',
            'menus_integration_dish_addons.*.branch_menu_id' => 'required|integer|exists:branch_menus,id',
            'menus_integration_dish_addons.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors(), 'input' => $input]);
            return RespondWithBadRequestWithData($validator->errors());
        }

        $branch_menu_category = BranchMenuAddon::with('menusIntegrationDishAddons')->find($id);
        if (!$branch_menu_category) {
            return  RespondWithBadRequestNotExist();
        }

        // $user_id =  Auth::guard('admin')->user()->id;
        // $branch_menu_category = BranchMenuAddon::findOrFail($id);
        $branch_menu_category->price = $request->price;
        $branch_menu_category->is_active = $request->is_active;

        $branch_menu_category->modified_by = authActionSave()['by'];
        $branch_menu_category->modified_by_type = authActionSave()['type'];
        $branch_menu_category->save();


        if ($request->filled('menus_integration_dish_addons') && count($request->menus_integration_dish_addons) > 0) {
            foreach ($request->menus_integration_dish_addons as $menu_dish) {
                // Skip empty rows (in case user added blank)
                if (empty($menu_dish['menus_integration_id']) && empty($menu_dish['id'])) {
                    continue;
                }

                // Shared data
                $data = [
                    'price' => $menu_dish['price'] ?? 0,
                ];

                if (!empty($menu_dish['id'])) {
                    //Update existing integration
                    $edit_menu_dish = MenusIntegrationDishAddon::find($menu_dish['id']);
                    if ($edit_menu_dish) {
                        $data['modified_by'] = authActionSave()['by'];
                        $data['modified_by_type'] = authActionSave()['type'];
                        $edit_menu_dish->update($data);
                    }
                } else {
                    $edit_menu_dish = MenusIntegrationDishAddon::where('branch_menu_addon_id',$id)->where('menus_integration_id', $menu_dish['menus_integration_id'])->first();
                    if ($edit_menu_dish) {
                        return respondError(__('branch_menu_addon.alraedy_added'), 404);
                    }else{
                        //Create new integration if doesn't exist
                        $data['menus_integration_id'] = $menu_dish['menus_integration_id'];
                        $data['branch_menu_id'] = $menu_dish['branch_menu_id'];
                        // $data['branch_menu_addon_id'] = $menu_dish['menus_addon_id'];
                        $data['branch_menu_addon_id'] = $id;
                        $data['created_by'] = authActionSave()['by'];
                        $data['created_by_type'] = authActionSave()['type'];
                        MenusIntegrationDishAddon::create($data);
                    }
                }
            }
        }

        $branch_menu_category->refresh();

        return ResponseWithSuccessData($this->lang, $branch_menu_category, 1);
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function change_status($id)
    {
        $active = 1;
        $branch_menu = BranchMenuAddon::findOrFail($id);
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
