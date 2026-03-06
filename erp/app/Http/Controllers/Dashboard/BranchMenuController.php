<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Branch;
use App\Models\Country;
use App\Models\BranchMenu;
use App\Models\MenusIntegration;

use Illuminate\Http\Request;
use App\Services\KitchenServices\BranchMenuService;
use App\Http\Controllers\Controller;

class BranchMenuController extends Controller
{
    protected $branchMenuService;

    public function __construct(BranchMenuService $branchMenuService)
    {
        $this->branchMenuService = $branchMenuService;
    }

    public function index(Request $request)
    {
        $response = $this->branchMenuService->index($request);
        $responseData = $response->original;
        $branch_menu_categories = $responseData['data'];
        $branches = Branch::all();
        $all_menu_integrations = MenusIntegration::all();
        return view('dashboard.branch.branch_menu.list', compact('branch_menu_categories', 'branches', 'all_menu_integrations'));
    }

    public function show($id)
    {
        $response = $this->branchMenuService->show($id);
        $responseData = $response->original;
        return $branch_menu = $responseData['data'];
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $response = $this->branchMenuService->update($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('branch.menus.list')->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('branch.menus.list')->with('message', $message);
    }

    public function change_status(Request $request, $id)
    {
        $response = $this->branchMenuService->change_status($id);
        $responseData = $response->original;
        return $branch_menu = $responseData['data'];
    }
    public function show_branch($branch_id)
    {
        $branch_menu_categories = $this->branchMenuService->branch($branch_id)->get();
        $branches = Branch::all();
        $all_menu_integrations = MenusIntegration::all();
        return view('dashboard.branch.branch_menu.list', compact('branch_menu_categories', 'branches', 'all_menu_integrations'));
    }

    // public function show_branch($branch_id)
    // {
    //     $branch_menu_categories = BranchMenu::where('branch_id', $branch_id)
    //         ->with(['branches', 'branchMenuCategories.dish_categories', 'dish'])
    //         ->get();

    //     $branches = Branch::all();

    //     return view('dashboard.branch.branch_menu.list', compact('branch_menu_categories', 'branches'));
    // }
}
