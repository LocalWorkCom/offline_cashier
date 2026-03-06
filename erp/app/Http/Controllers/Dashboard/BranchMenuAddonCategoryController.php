<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Services\KitchenServices\BranchMenuAddonCategoryService;
use Illuminate\Http\Request;

class BranchMenuAddonCategoryController extends Controller
{
    protected $branchMenuAddonCategoryService;

    public function __construct(BranchMenuAddonCategoryService $branchMenuAddonCategoryService)
    {
        $this->branchMenuAddonCategoryService = $branchMenuAddonCategoryService;
    }

    public function index(Request $request)
    {
        $response = $this->branchMenuAddonCategoryService->index($request);
        $responseData = $response->original;
        $branch_menu_categories = $responseData['data'];
        $branches = Branch::all();
        return view('dashboard.branch.branch_menu_addon_category.list', compact('branch_menu_categories', 'branches'));
    }

    public function show($id)
    {
        $response = $this->branchMenuAddonCategoryService->show($id);
        $responseData = $response->original;
        return $branch_menu_addon_category = $responseData['data'];
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $response = $this->branchMenuAddonCategoryService->edit($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('branch.menu.addon.categories.list')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('branch.menu.addon.categories.list')->with('message', $message);
    }
    public function change_status(Request $request, $id)
    {
        $response = $this->branchMenuAddonCategoryService->change_status($id);
        $responseData = $response->original;
        return $branch_menu_addon_category = $responseData['data'];
    }

    public function show_branch($branch_id)
    {
        $branch_menu_categories = $this->branchMenuAddonCategoryService->branch($branch_id)->get();
        $branches = Branch::all();
        return view('dashboard.branch.branch_menu_addon_category.list', compact('branch_menu_categories', 'branches'));
    }
}
