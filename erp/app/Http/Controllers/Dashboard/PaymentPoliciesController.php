<?php

namespace App\Http\Controllers\Dashboard;

use App\Models\Branch;
use Illuminate\Http\Request;
use App\Models\PaymentPolicies;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Services\SettingsServices\PaymentPoliciesService;

class PaymentPoliciesController extends Controller
{
    protected $paymentPoliciesService;
    protected $checkToken;

    public function __construct(PaymentPoliciesService $paymentPoliciesService)
    {
        $this->paymentPoliciesService = $paymentPoliciesService;
        $this->checkToken = false;
    }
    public function index(Request $request)
    {
        // Get the query builder from service
        $query = $this->paymentPoliciesService->index($request,false);

        // Execute the query
        $paymentPolicies = $query->get();

        // You already eager-loaded 'branch', so no need to refetch
        foreach ($paymentPolicies as $policy) {
            if (!$policy->relationLoaded('branch') || !$policy->branch) {
                $policy->setRelation('branch', new Branch());
            }
        }

        return view('dashboard.payment_policies.list', compact('paymentPolicies'));
    }

    public function create()
    {
        $query = DB::table('branches')->whereNull('deleted_at');

        // If user is Branch Manager, only show their branch
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('id', $branch_id);
            }
        }

        $branches = $query->get();

        $branchOrderTypes = DB::table('payment_policies')
            ->whereNull('deleted_at')
            ->select('branch_id', 'order_type')
            ->get()
            ->groupBy('branch_id');

        return view('dashboard.payment_policies.add', compact('branches', 'branchOrderTypes'));
    }

    public function store(Request $request)
    {
        $response = $this->paymentPoliciesService->store($request, $this->checkToken);
        return redirect()->route('payment_policies.list')->with('message', __('payment_policies.add done'));
    }

    public function show($id)
    {
        $paymentPolicy = PaymentPolicies::with(['branch', 'invoiceCount'])->findOrFail($id);
        return view('dashboard.payment_policies.show', compact('paymentPolicy', 'id'));
    }

    public function edit($id)
    {
        $paymentPolicy = PaymentPolicies::with(['invoiceCount', 'branch'])->findOrFail($id);

        $query = DB::table('branches')->whereNull('deleted_at');

        // If user is Branch Manager, only show their branch
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('id', $branch_id);
            }
        }

        $branches = $query->get();

        $branchOrderTypes = DB::table('payment_policies')
            ->whereNull('deleted_at')
            ->select('branch_id', 'order_type')
            ->get()
            ->groupBy('branch_id');

        return view('dashboard.payment_policies.edit', compact('paymentPolicy', 'branchOrderTypes', 'branches', 'id'));
    }

    public function update(Request $request, $id)
    {
        $response = $this->paymentPoliciesService->update($request, $id, $this->checkToken);

        return redirect()->route('payment_policies.list')->with('message', __('payment_policies.update done'));
    }

    public function delete(Request $request, $id)
    {
        $response = $this->paymentPoliciesService->destroy($id, app()->getLocale());

        return redirect()->route('payment_policies.list')->with('message', __('payment_policies.delete done'));
    }
}
