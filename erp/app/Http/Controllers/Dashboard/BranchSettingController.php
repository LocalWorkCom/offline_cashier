<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\BranchSetting;
use App\Models\PaymentPolicies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\BranchSettingService;

class BranchSettingController extends Controller
{
    protected $BranchSettingService;
    protected $checkToken;

    public function __construct(BranchSettingService $BranchSettingService)
    {
        $this->BranchSettingService = $BranchSettingService;
        $this->checkToken = false;
    }

    public function index(Request $request)
    {
        $response = $this->BranchSettingService->index($request, $this->checkToken);

        // Decode the JSON response
        // $responseData = json_decode($response->getContent(), true);

        // Hydrate payment policies
        $BranchSetting = $response->get();

        // Convert 'branch' from array to Eloquent model for each branchSetting
        // foreach ($BranchSetting as $branchSetting) {
        //     $branchSetting->branch = Branch::find($branchSetting->branch_id) ?? new Branch(); // Prevents null errors
        // }

        return view('dashboard.branch_settings.list', compact('BranchSetting'));
    }
    public function getPaymentPolicies(Request $request)
    {
        $branchId = $request->branch_id;
        $orderType = $request->order_type;

        $policy = DB::table('payment_policies')
            ->where('branch_id', $branchId)
            ->when($orderType, function ($query) use ($orderType) {
                return $query->where('order_type', $orderType);
            })
            ->whereNull('deleted_at')
            ->first();

        return response()->json([
            'deposit_required' => $policy->deposit_required ?? 1, // Default 1 (visible)
            'table_cancelation_value_type' => $policy->table_cancelation_value_type ?? 1 // Default 1 (visible)
        ]);
    }
    public function create()
    {
        $branches = Branch::with(['employess'])
            ->whereNull('deleted_at')
            ->whereIn('id', function ($query) {
                $query->select('branch_id')
                    ->from('payment_policies')
                    ->whereNull('deleted_at');
            })
            ->whereNotIn('id', function ($query) {
                $query->select('branch_id')
                    ->from('branch_settings')
                    ->whereNull('deleted_at');
            })
            ->get();

        // Get all order types for all branches (we'll use this in JavaScript)
        $allOrderTypes = PaymentPolicies::whereNull('deleted_at')
            ->get()
            ->groupBy('branch_id')
            ->map(function ($item) {
                return $item->pluck('order_type')->unique();
            });

        return view('dashboard.branch_settings.add', [
            'branches' => $branches,
            'allOrderTypes' => $allOrderTypes
        ]);
    }
    public function store(Request $request)
    {
        $response = $this->BranchSettingService->add($request, $this->checkToken);
        $responseData = $response->original;

        if (!$responseData['status'] && isset($responseData['data'])) {
            return redirect()->back()->withErrors($responseData['data'])->withInput();
        }

        return redirect()->route('branch_settings.list')->with('message', $responseData['message']);
    }

    public function show($id)
    {
        $branchSetting = BranchSetting::with('branch')->findOrFail($id);

        // Get computed order_type (via accessor)
        $orderType = $branchSetting->order_type;

        // Get the payment policy for this branch and order type
        $paymentPolicy = null;
        if ($orderType) {
            $paymentPolicy = \App\Models\PaymentPolicies::where('branch_id', $branchSetting->branch_id)
                ->where('order_type', $orderType)
                ->whereNull('deleted_at')
                ->get();
        }

        return view('dashboard.branch_settings.show', [
            'branchSetting' => $branchSetting,
            'id' => $id,
            'orderType' => $orderType,
            'paymentPolicy' => $paymentPolicy
        ]);
    }

    public function edit($id)
    {
        $branchSetting = BranchSetting::findOrFail($id);

        $branches = DB::table('branches')
            ->whereNull('deleted_at')
            ->whereIn('id', function ($query) {
                $query->select('branch_id')
                    ->from('payment_policies')
                    ->whereNull('deleted_at');
            })
            ->whereNotIn('id', function ($query) {
                $query->select('branch_id')
                    ->from('branch_settings')
                    ->whereNull('deleted_at');
            })
            ->orWhere('id', $branchSetting->branch_id) // Include current branch
            ->get();

        // Get all order types grouped by branch_id
        $allOrderTypes = PaymentPolicies::whereNull('deleted_at')
            ->get()
            ->groupBy('branch_id')
            ->map(function ($item) {
                return $item->pluck('order_type')->unique()->values(); // return clean array of order types
            });

        $orderType = $branchSetting->order_type ?? null;

        $paymentPolicy = null;
        if ($orderType) {
            $paymentPolicy = PaymentPolicies::where('branch_id', $branchSetting->branch_id)
                ->where('order_type', $orderType)
                ->whereNull('deleted_at')
                ->first();
        }

        return view('dashboard.branch_settings.edit', [
            'branchSetting' => $branchSetting,
            'branches' => $branches,
            'id' => $id,
            'paymentPolicy' => $paymentPolicy,
            'orderType' => $orderType,
            'allOrderTypes' => $allOrderTypes // ✅ pass this to view
        ]);
    }


    public function update(Request $request, $id)
    {
        $response = $this->BranchSettingService->update($request, $id, $this->checkToken);
        $responseData = $response->original;

        if (!$responseData['status'] && isset($responseData['data'])) {
            return redirect()->back()->withErrors($responseData['data'])->withInput();
        }

        return redirect()->route('branch_settings.list')->with('message', $responseData['message']);
    }

    public function delete(Request $request, $id)
    {
        $response = $this->BranchSettingService->destroy($request, $id, $this->checkToken);
        return redirect()->route('branch_settings.list')->with('message', $response->original['message']);
    }
}
