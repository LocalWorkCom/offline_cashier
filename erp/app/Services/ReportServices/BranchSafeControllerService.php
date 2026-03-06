<?php


namespace App\Services\ReportServices;

use App\Models\BranchSafe;
use App\Models\EmployeeOpeningBalance;

class BranchSafeControllerService
{
    private $lang;
    public function __construct()
    {
        $this->lang = app()->getLocale();
        app()->setLocale($this->lang);
    }
    public function index()
    {
        $lang = app()->getLocale();
        // if (!CheckToken() && $checkToken) {
        //     return RespondWithBadRequest($lang, 5);
        // }

        $query = BranchSafe::with(['branch', 'employee','creator'])->get();
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }
        $balances = [];
        // $safes = $query->get();
        // dd($query);
        foreach ($query as $safe) {
            $branchSafe = json_decode($safe->balances_ids);
            foreach ($branchSafe as $balance) {
                // dd($balance);
                $balances[] = EmployeeOpeningBalance::where('id', $balance)->first();
            }
        }
        // dd($balances);
        $data = [
            'query' => $query,
            'balances' => $balances,
        ] ;
        return $data;
    }
    public function show($id)
    {
        $lang = app()->getLocale();
        // if (!CheckToken() && $checkToken) {
        //     return RespondWithBadRequest($lang, 5);
        // }

        $query = BranchSafe::with(['branch', 'employee','creator','machine'])->where('id' ,$id)->get();
        if (auth('admin')->user()->hasRole('Branch Manager')) {
            $branch_id = getBranchManagerID();
            if ($branch_id) {
                $query->where('branch_id', $branch_id);
            }
        }
        $balances = [];
        // $safes = $query->get();
        // dd($query);
        foreach ($query as $safe) {
            $branchSafe = json_decode($safe->balances_ids);
            foreach ($branchSafe as $balance) {
                // dd($balance);
                $balances[] = EmployeeOpeningBalance::where('id', $balance)->first();
            }
        }
        // dd($balances);
        $data = [
            'query' => $query,
            'balances' => $balances,
        ] ;
        return $data;
    }
}
