<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Country;
use App\Models\Employee;
use App\Models\City;
use App\Models\Area;
use App\Models\BranchRegion;
use App\Services\SettingsServices\BranchService;
use Illuminate\Http\Request;
use App\Traits\KitchenLogTrait;
use App\Models\CompanyProfileSetting;

class BranchController extends Controller
{
    use KitchenLogTrait;

    protected $branchService;

    public function __construct(BranchService $branchService)
    {
        $this->branchService = $branchService;
    }

    public function index(Request $request)
    {
        $response = $this->branchService->index($request);
        $responseData = $response->original;
        $branches = $responseData['data'];
        return view('dashboard.branch.list', compact('branches'));
    }

    public function create()
    {
        $countries = Country::all();
        $employees = Employee::whereIn('flag', ['branch manager', 'employee'])
            ->whereNotIn('id', function ($query) {
                $query->select('employee_id')
                    ->from('branches')
                    ->whereNotNull('employee_id'); // exclude only branches that already have a manager
            })
            ->get();
        $branch_region_ids = BranchRegion::get()->pluck('id');
        $branch_regions = Area::whereNotIn('id', $branch_region_ids)->get();
        $company_profile_settings = CompanyProfileSetting::get();
        return view('dashboard.branch.add', compact('countries', 'employees', 'branch_regions', 'company_profile_settings'));
    }
    public function store(Request $request)
    {
        $response = $this->branchService->store($request);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()
                ->withErrors($validationErrors)
                ->withInput()
                ->with('old_times', $request->time ?? []) // Add this
                ->with('old_regions', $request->region ?? []); // Add this
        }
        $message = $responseData['message'];
        return redirect()->route('branches.list')->with('message', $message);
    }
    public function show($id)
    {
        $lang = session()->get('locale');

        try {
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $managerBranchId = getBranchManagerID();
                if ($id != $managerBranchId) {
                    abort(403, __('messages.forbidden'));
                }
            }
            $response = $this->branchService->show($id);
            $responseData = $response->original;
            $branch = $responseData['data'];

            return view('dashboard.branch.show', compact('branch'));
        } catch (\Exception $e) {
            return back()->withErrors([__('messages.forbidden')]);
        }
    }

    public function show_branch_region($city_id, $branch_id)
    {
        $response = $this->branchService->show_branch_region($city_id, $branch_id);
        $responseData = $response->original;
        // \Log::debug('Branch Region Data:', ['data' => $responseData['data']['branch_regions']]);
        return $regions = $responseData['data'];
        return view('dashboard.branch.show', compact('regions'));
    }

    public function sync($id)
    {
        try {
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $managerBranchId = getBranchManagerID();
                if ($id != $managerBranchId) {
                    abort(403, __('messages.forbidden'));
                }
            }

            $response = $this->branchService->sync($id);
            $responseData = $response->original;
            $message = $responseData['message'];

            return redirect()->route('branches.list')->with('message', $message);
        } catch (\Exception $e) {
            return back()->withErrors([__('messages.forbidden')]);
        }
    }


    public function edit($id)
    {
        try {
            if (auth('admin')->user()->hasRole('Branch Manager')) {
                $managerBranchId = getBranchManagerID();
                if ($id != $managerBranchId) {
                    abort(403, __('messages.forbidden'));
                }
            }

            // Proceed with editing if the user is authorized
            $branch = Branch::findOrFail($id);
            $countries = Country::all();
            $cities = City::where('country_id', $branch->country_id)->get();
            $regions = Area::where('city_id', $branch->city_id)->get();
            $currentManagerId = $branch->employee_id; // the manager assigned to this branch

            $employees = Employee::whereIn('flag', ['branch manager', 'employee'])
                ->where(function ($query) use ($currentManagerId) {
                    $query->whereNotIn('id', function ($sub) use ($currentManagerId) {
                        $sub->select('employee_id')
                            ->from('branches')
                            ->whereNotNull('employee_id')
                            ->when($currentManagerId, function ($q) use ($currentManagerId) {
                                $q->where('employee_id', '<>', $currentManagerId);
                            });
                    });
                })
                ->get();

            $branch_region_ids = BranchRegion::where('branch_id', '!=', $branch->id)->get()->pluck('id');
            $company_profile_settings = CompanyProfileSetting::get();

            return view('dashboard.branch.edit', compact('branch', 'countries', 'employees', 'cities', 'regions', 'company_profile_settings'));
        } catch (\Exception $e) {
            return back()->withErrors([__('messages.forbidden')]);
        }
    }


    public function update(Request $request, $id)
    {
        $response = $this->branchService->update($request, $id);
        $responseData = $response->original;
        if (!$responseData['status'] && isset($responseData['data'])) {
            $validationErrors = $responseData['data'];
            return redirect()->back()->withErrors($validationErrors)->withInput();
        }
        $message = $responseData['message'];
        return redirect()->route('branches.list')->with('message', $message);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->branchService->destroy($request, $id);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect()->route('branches.list')->with('message', $message);
    }

    public function change_status(Request $request, $id)
    {
        $response = $this->branchService->change_status($id);
        $responseData = $response->original;
        return $branch_menu_category = $responseData['data'];
    }

    public function orders($id)
    {
        return $this->add_order_in_kitchen($id);
    }

    public function orders_status($id, $status)
    {
        return $this->change_status_in_kitchen($id, $status);
    }

    public function getBranchesByCompany($companyId)
    {
        // Fetch branches where the company_id matches the selected company
        $branches = Branch::where('company_profile_setting_id', $companyId)->get(['id', 'name_ar']);

        // Return the branches as a JSON response
        return response()->json($branches);
    }
}
