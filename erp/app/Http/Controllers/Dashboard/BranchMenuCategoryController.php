<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Services\KitchenServices\BranchMenuCategoryService;
use Illuminate\Http\Request;

class BranchMenuCategoryController extends Controller
{
    protected $branchMenuCategoryService;

    public function __construct(BranchMenuCategoryService $branchMenuCategoryService)
    {
        $this->branchMenuCategoryService = $branchMenuCategoryService;
    }

    public function index(Request $request)
    {
        $response = $this->branchMenuCategoryService->index($request);
        $responseData = $response->original;
        $branch_menu_categories = $responseData['data'];
        $branches = Branch::all();
        return view('dashboard.branch.branch_menu_category.list', compact('branch_menu_categories', 'branches'));
    }

    public function show($id)
    {

        $response = $this->branchMenuCategoryService->show($id);
        $responseData = $response->original;
        return $branch_menu_category = $responseData['data'];
    }

    public function store(Request $request)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $response = $this->branchMenuCategoryService->edit($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->route('branch.categories.list')->withErrors($validationErrors)->withInput();
        }
        $message= $responseData['message'];
        return redirect()->route('branch.categories.list')->with('message',$message);
    }

    public function change_status(Request $request, $id)
    {
        $response = $this->branchMenuCategoryService->change_status($id);
        $responseData = $response->original;
        return $branch_menu_category = $responseData['data'];
    }

   public function show_branch($branch_id)
    {
        $branch_menu_categories = $this->branchMenuCategoryService->branch($branch_id)->get();
        $branches = Branch::all();
        return view('dashboard.branch.branch_menu_category.list', compact('branch_menu_categories', 'branches'));
    }

}
