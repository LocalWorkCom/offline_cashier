<?php

namespace App\Http\Controllers\Api\ClientAPIs;

use App\Http\Controllers\Controller;
use App\Models\BranchMenuCategory;
use Illuminate\Http\Request;

class MenuHomeController extends Controller
{
    protected $lang;
    public function index(Request $request){
        $this->lang = $request->header('lang','ar');
        $menuCategories = BranchMenuCategory::with(['dish_categories' => function ($query) {
            $query->where('is_active', true);
        }, 'dish_categories.dishes' => function ($query) {
            $query->where('is_active', true);
        }])->get();
        if (!$menuCategories) {
            return RespondWithBadRequestData($this->lang, 2);
        }
        return ResponseWithSuccessData($this->lang, $menuCategories, 1);

    }
}
