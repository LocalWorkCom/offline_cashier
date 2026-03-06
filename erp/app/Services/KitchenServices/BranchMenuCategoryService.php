<?php

namespace App\Services\KitchenServices;

use App\Models\BranchMenuCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BranchMenuCategoryService
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
            $query = BranchMenuCategory::with(['branches', 'dish_categories']);
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
        return BranchMenuCategory::where('branch_id', $id)
            ->with(['branches', 'dish_categories']);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $branch_menu_category = BranchMenuCategory::with(['branches', 'dish_categories'])
            ->findOrFail($id);

        return ResponseWithSuccessData($this->lang, $branch_menu_category, 1);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            // Validation
            $validator = Validator::make($request->all(), [
                'is_active' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return RespondWithBadRequestWithData($validator->errors());
            }

            $check_branch_menu_category = BranchMenuCategory::find($id);
            if (!$check_branch_menu_category) {
                return  RespondWithBadRequestNotExist();
            }

            $user_id =  Auth::guard('admin')->user()->id;
            $branch_menu_category = BranchMenuCategory::findOrFail($id);
            $branch_menu_category->is_active = $request->is_active;
            $branch_menu_category->modified_by = $user_id;
            $branch_menu_category->save();

            return ResponseWithSuccessData($this->lang, $branch_menu_category, 1);
        } catch (\Exception $e) {
            Log::error('Error updating branch menu category: ' . $e->getMessage());
            return RespondWithBadRequestData($this->lang, 2);
        }
    }

    /**
     * Soft delete the specified resource from storage.
     */
    public function change_status($id)
    {
            // $user_id =  Auth::guard('admin')->user()->id;
            $active = 1;
            $branch_menu_category = BranchMenuCategory::findOrFail($id);
            if ($branch_menu_category->is_active == 1) {
                $active = 0;
            }
            $branch_menu_category->is_active = $active;
            $branch_menu_category->modified_by = authActionSave()['by'];
            $branch_menu_category->modified_by_type = authActionSave()['type'];
            $branch_menu_category->save();
            return ResponseWithSuccessData($this->lang, $branch_menu_category, 1);
       
    }
}
