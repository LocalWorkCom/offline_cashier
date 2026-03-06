<?php

namespace App\Services\KitchenServices;

use App\Models\BranchMenuSize;
use App\Models\MenusIntegrationDishSize;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchMenuSizeService
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
            $query = BranchMenuSize::with(['branches', 'dishSizes', 'dishes']);
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $branch_id = getBranchManagerID();
                if ($branch_id) {
                    $query->where('branch_id', $branch_id);
                }
            }
            $branch_menu_sizes = $query->get();
            return ResponseWithSuccessData($this->lang, $branch_menu_sizes, 1);
        } catch (\Exception $e) {
            Log::error('Error fetching branches: ' . $e->getMessage());
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    public function branch($id, $asQuery = false)
    {
        $branch_menu_sizes = BranchMenuSize::where('branch_id', $id)->with(['branches', 'dishSizes', 'dishes']);
        if ($asQuery) {
            return $branch_menu_sizes;
        }

        return ResponseWithSuccessData($this->lang, $branch_menu_sizes, 1);
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
        $branch_menu_size = BranchMenuSize::with(['branches', 'dishSizes', 'dishes', 'menusIntegrationDishSizes.menusIntegrations'])->findOrFail($id);
        return ResponseWithSuccessData($this->lang, $branch_menu_size, 1);
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
            'menus_integration_dish_sizes' => 'nullable|array',
            'menus_integration_dish_sizes.*.id' => 'nullable|integer|exists:menus_integration_dish_sizes,id',
            'menus_integration_dish_sizes.*.menus_integration_id' => 'required|integer|exists:menus_integrations,id',
            'menus_integration_dish_sizes.*.branch_menu_id' => 'required|integer|exists:branch_menus,id',
            'menus_integration_dish_sizes.*.price' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', ['errors' => $validator->errors(), 'input' => $input]);
            return RespondWithBadRequestWithData($validator->errors());
        }
        $branch_menu_size = BranchMenuSize::with('menusIntegrationDishSizes')->find($id);
        if (!$branch_menu_size) {
            return  RespondWithBadRequestNotExist();
        }

        // $user_id =  Auth::guard('admin')->user()->id;
        // $branch_menu_size = BranchMenuSize::findOrFail($id);
        $branch_menu_size->price = $request->price;
        $branch_menu_size->is_active = $request->is_active;
        $branch_menu_size->modified_by = authActionSave()['by'];
        $branch_menu_size->modified_by_type = authActionSave()['type'];
        $branch_menu_size->save();

        if ($request->filled('menus_integration_dish_sizes') && count($request->menus_integration_dish_sizes) > 0) {
            foreach ($request->menus_integration_dish_sizes as $menu_dish) {
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
                    $edit_menu_dish = MenusIntegrationDishSize::find($menu_dish['id']);
                    if ($edit_menu_dish) {
                        $data['modified_by'] = authActionSave()['by'];
                        $data['modified_by_type'] = authActionSave()['type'];
                        $edit_menu_dish->update($data);
                    }
                } else {
                    $edit_menu_dish = MenusIntegrationDishSize::where('branch_menu_size_id', $id)->where('menus_integration_id', $menu_dish['menus_integration_id'])->first();
                    if ($edit_menu_dish) {
                        return respondError(__('branch_menu_size.alraedy_added'), 404);
                    }else{
                        //Create new integration if doesn't exist
                        $data['menus_integration_id'] = $menu_dish['menus_integration_id'];
                        $data['branch_menu_id'] = $menu_dish['branch_menu_id'];
                        // $data['branch_menu_size_id'] = $menu_dish['menus_size_id'];
                        $data['branch_menu_size_id'] = $id;
                        $data['created_by'] = authActionSave()['by'];
                        $data['created_by_type'] = authActionSave()['type'];
                        MenusIntegrationDishSize::create($data);
                    }
                }
            }
        }

        $branch_menu_size->refresh();
        return ResponseWithSuccessData($this->lang, $branch_menu_size, 1);
    }

    /**
     * Soft delete the specified resource from storage.
     */

    public function change_status($id)
    {
        $active = 1;
        $branch_menu_size = BranchMenuSize::findOrFail($id);
        if ($branch_menu_size->is_active == 1) {
            $active = 0;
        }
        $branch_menu_size->is_active = $active;
        $branch_menu_size->modified_by = authActionSave()['by'];
        $branch_menu_size->modified_by_type = authActionSave()['type'];
        $branch_menu_size->save();
        return ResponseWithSuccessData($this->lang, $branch_menu_size, 1);
    }
}
