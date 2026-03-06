<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\CashPaymentSetting;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CashPaymentSettingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $isSuperAdmin = User::find(auth('admin')->user()->id)->where('flag','admin')->first() != null && Employee::where('user_id',auth('admin')->user()->id)->where('flag','branch manager')->first() == null;

        if ($isSuperAdmin) {
            $cash_payment_settings = CashPaymentSetting::get();
        }else
        {
            $branchId = Employee::where('user_id',auth('admin')->user()->id)->where('flag','branch manager')->first()?->branch_id;
            $cash_payment_settings = CashPaymentSetting::where('branch_id',$branchId)->get();
        }
//        dd($cash_payment_settings->count() >= 1);
        return view('dashboard.cash_payment_setting.list', compact('cash_payment_settings', 'isSuperAdmin'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branches = Branch::get();
        $managerBranchId = Employee::where('user_id',auth('admin')->user()->id)->where('flag','branch manager')->first()?->branch_id;
        $isSuperAdmin = User::find(auth('admin')->user()->id)->where('flag','admin')->first() != null && Employee::where('user_id',auth('admin')->user()->id)->where('flag','branch manager')->first() == null;

//        dd($branches);
        return view('dashboard.cash_payment_setting.add', compact('branches', 'managerBranchId', 'isSuperAdmin'));
//        if ($validator->fails()) {
//            return respondError(($lang == 'en' ? 'Validation Error.' : 'خطأ في التحقق.'), 400, $validator->errors());
//        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
//        dd($request->all());
        $lang = app()->getLocale();
        $data = Validator::make($request->all(), [
            'branch_id' => [
                'required',
                'numeric',
                'min:1',
                'exists:branches,id',
                Rule::unique('cash_payment_settings', 'branch_id')->whereNull('deleted_at'),
            ],
            'min_cash' => 'required|numeric|min:0',
            'max_cash' => 'required|numeric|min:0',
            'enforce_limit' => 'nullable|boolean|in:0,1',
        ]);
//        dd($data->errors());
        if ($data->fails()) {
            return redirect()->back()->withErrors($data)->withInput();
        }
        $cash_payment_setting = new CashPaymentSetting();
        $cash_payment_setting->branch_id = $request->branch_id;
        $cash_payment_setting->min_cash = $request->min_cash;
        $cash_payment_setting->max_cash = $request->max_cash;
        $cash_payment_setting->enforce_limit = $request->enforce_limit;
        $cash_payment_setting->created_by = auth('admin')->id() ?? null ;
        $cash_payment_setting->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message= $responseData['message'];
        return redirect('dashboard/cash-settings')->with('message',$message);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $cash_payment_setting = CashPaymentSetting::findOrFail($id);
        return view('dashboard.cash_payment_setting.show', compact('cash_payment_setting'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $cash_payment_setting = CashPaymentSetting::findOrFail($id);
        $managerBranchId = Employee::where('user_id',auth('admin')->user()->id)->where('flag','branch manager')->first()?->branch_id;
        $isSuperAdmin = User::find(auth('admin')->user()->id)->where('flag','admin')->first() != null && Employee::where('user_id',auth('admin')->user()->id)->where('flag','branch manager')->first() == null;

        return view('dashboard.cash_payment_setting.edit', compact('cash_payment_setting', 'managerBranchId', 'isSuperAdmin'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $lang = app()->getLocale();
        $data = Validator::make($request->all(), [
            'branch_id' => [
                'required',
                'numeric',
                'min:1',
                'exists:branches,id',
                Rule::unique('cash_payment_settings', 'branch_id')->ignore($id)->whereNull('deleted_at'),
            ],
            'min_cash' => 'required|numeric|min:0',
            'max_cash' => 'required|numeric|min:0',
            'enforce_limit' => 'nullable|boolean|in:0,1',
        ]);
        if ($data->fails()) {
            return redirect()->back()->withErrors($data)->withInput();
        }
        $cash_payment_setting = CashPaymentSetting::find($id);
        $cash_payment_setting->branch_id = $request->branch_id;
        $cash_payment_setting->min_cash = $request->min_cash;
        $cash_payment_setting->max_cash = $request->max_cash;
        $cash_payment_setting->enforce_limit = $request->enforce_limit;
        $cash_payment_setting->modified_by = auth('admin')->id() ?? null;
        $cash_payment_setting->save();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/cash-settings')->with('message', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $lang = app()->getLocale();
        $cash_payment_setting = CashPaymentSetting::findOrFail($id);
        $cash_payment_setting->deleted_by = auth('admin')->id() ?? null;
        $cash_payment_setting->save();
        $cash_payment_setting->delete();
        $response = RespondWithSuccessRequest($lang, 1);
        $responseData = $response->original;
        $message = $responseData['message'];
        return redirect('dashboard/cash-settings')->with('message', $message);
    }
}
