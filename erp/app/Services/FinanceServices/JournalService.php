<?php

namespace App\Services\FinanceServices;

use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Http\Resources\Finance\JournalResource;
use App\Imports\JournalsImport;
use App\Models\Branch;
use App\Models\CompanyProfileSetting;
use App\Models\Currency;
use App\Models\JournalEntryDetails;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\JournalsExport;

class JournalService
{
    public function getAll(Request $request)
    {
        $employee = auth('employee')->user();
        $facility_id = $employee->employeeFacility->facility_id;

        $query = Journal::with('facility', 'currency', 'children')->where('facility_id', $facility_id)->where('parent_id', null);

        if ($request->filled('name')) {
            $name = $request->name;
            $query->where(function ($q) use ($name) {
                $q->where('name_en', 'LIKE', "%{$name}%")
                ->orWhere('name_ar', 'LIKE', "%{$name}%")
                ->orWhere('code', 'LIKE', "%{$name}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        if ($request->filled('currency_id')) {
            $query->where('currency_id', $request->currency_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }
        return $query->orderBy('code', 'asc');
    }

    public function show(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = Journal::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            return ResponseWithSuccessData(request()->header('lang', 'ar'), new JournalResource($data), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function list(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $employee = auth('employee')->user();
            $facility_id = $employee->employeeFacility->facility_id;

            $type = $request->input('type');
            $flattenTree = function ($accounts) use ($lang, &$flattenTree) {
                $result = [];

                foreach ($accounts as $account) {
                    $result[] = [
                        'id'   => $account->id,
                        'name' => $lang === 'ar' ? $account->name_ar : $account->name_en,
                        'code' => $account->code,
                    ];

                    if ($account->childrenRecursive->isNotEmpty()) {
                        $result = array_merge($result, $flattenTree($account->childrenRecursive));
                    }
                }

                return $result;
            };

            if ($type === 0 || $type === '0') {
                $parents = Journal::query()
                    ->where('facility_id', $facility_id)
                    ->whereNull('parent_id')
                    ->where('level', 1)
                    ->orderBy('code')
                    ->get(['id', 'name_ar', 'name_en', 'code']);

                $journals = $parents->map(fn($j) => [
                    'id'   => $j->id,
                    'name' => $lang === 'ar' ? $j->name_ar : $j->name_en,
                    'code' => $j->code,
                ])->values();

                return ResponseWithSuccessData($lang, $journals, 1);
            }

            if ($type > 0) {
                $parent = Journal::with('childrenRecursive')
                    ->where('facility_id', $facility_id)
                    ->find($type);

                if (!$parent || $parent->childrenRecursive->isEmpty()) {
                    return ResponseWithSuccessData($lang, [], 1);
                }

                $journals = $flattenTree($parent->childrenRecursive);

                return ResponseWithSuccessData($lang, $journals, 1);
            }

            $roots = Journal::with('childrenRecursive')
                ->where('facility_id', $facility_id)
                ->whereNull('parent_id')
                ->where('level', 1)
                ->orderBy('code')
                ->get();

            $allAccounts = $flattenTree($roots);

            return ResponseWithSuccessData($lang, $allAccounts, 1);


        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function showList(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $employee = auth('employee')->user();
            $facility_id = $employee->employeeFacility->facility_id;

            $flattenTree = function ($accounts) use ($lang, &$flattenTree) {
                $result = [];

                foreach ($accounts as $account) {
                    if(count($account->journalEntryDetails) == 0){
                        $result[] = [
                            'id'       => $account->id,
                            'name'     => $lang === 'ar' ? $account->name_ar : $account->name_en,
                            'code'     => $account->code,
                            'parent_id'=> $account->parent_id,
                        ];

                        if ($account->childrenRecursive->isNotEmpty()) {
                            $result = array_merge($result, $flattenTree($account->childrenRecursive));
                        }
                    }

                }

                return $result;
            };

            // $roots = Journal::with(['childrenRecursive' => function($q){
            //         $q->whereDoesntHave('journalEntryDetails');
            //     }])
            $roots = Journal::with(['childrenRecursive'])
                // ->whereDoesntHave('journalEntryDetails')
                ->where('facility_id', $facility_id)
                ->whereNull('parent_id')
                ->where('level', 1)
                ->orderBy('code')
                ->get();

            $allTree = $flattenTree($roots);

            return ResponseWithSuccessData($lang, $allTree, 1);


        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function select_list(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $employee = auth('employee')->user();
            $facility_id = $employee->employeeFacility->facility_id;

            $data = Journal::where('is_active',1)
                ->when($request->type == "no_child", fn($q) => $q->whereDoesntHave('children'))
                ->when($request->currency_id != 0, function ($q) use ($request) {
                        $q->where(function ($sub) use ($request) {
                            $sub->where('currency_id', $request->currency_id)
                                ->orWhereNull('currency_id');
                        });
                    })
                ->where('facility_id', $facility_id)
                ->get();
            $journals = $data->map(function ($journal) use ($lang) {
                return[
                    'id' => $journal->id,
                    'name' => $lang === 'ar' ? $journal->name_ar : $journal->name_en,
                    'code' => $journal->code
                ];
            });

            return ResponseWithSuccessData(request()->header('lang', 'ar'), $journals, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function add(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $employee = auth('employee')->user();
            $facility_id = $employee->employeeFacility->facility_id;

            // $result = collect($request->validated())->toArray();
            $result = collect($request->validated())->except('facility_id')->toArray();
            // $parent = Journal::find($parent_id);
            $levelAndCode = Journal::generateCodeAndLevel($request->parent_id ?? null, $request['code'] ?? null);
            $Journal = Journal::create(array_merge(
                $result,
                [
                    'is_active' => 1,
                    // 'parent_id' => $parent->id,
                    // 'type' => $parent->type,
                    'facility_id' => $facility_id,
                    'level' => $levelAndCode['level'],
                    // 'code' => $levelAndCode['code'],
                    'created_by' => authActionSave()['by'],
                    'created_by_type' => authActionSave()['type'],
                ]
            ));

            return $Journal;
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function edit(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $journal = Journal::find($request->id);
            if (!$journal) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            $data = $request->validated();

            $hasChildren = $journal?->children->isNotEmpty() ?? false;
            $hasEntries  = $journal?->journalEntryDetails->isNotEmpty() ?? false;

            $isProtected = $hasChildren || $hasEntries;
                $result = [
                    'name_ar'          => $data['name_ar'] ?? $journal->name_ar,
                    'name_en'          => $data['name_en'] ?? $journal->name_en,
                    'description_ar'   => $data['description_ar'] ?? $journal->description_ar,
                    'modified_by'      => authActionSave()['by'],
                    'modified_by_type' => authActionSave()['type'],
                ];

            if(!$isProtected){
                $levelAndCode = Journal::generateCodeAndLevel($request->parent_id ?? null, $request['code'] ?? null);
                $result += [
                    'level'            => $levelAndCode['level'],
                    'type'             => $data['type'] ?? $journal->type,
                    'account_type'     => $data['account_type'] ?? $journal->account_type,
                    'parent_id'        => $data['parent_id'] ?? $journal->parent_id,
                    'currency_id'      => $data['currency_id'] ?? $journal->currency_id,
                    'code'             => $data['code'] ?? $journal->code,
                ];
            }

            $journal->update($result);

            return ResponseWithSuccessData($lang, new JournalResource($journal), 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function archive(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
            $journal = Journal::find($request->id);
            if (!$journal) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            $is_active = $request->has('is_active') ? (int)$request->is_active : 0;
            $updateData = [
                'is_active'        => $is_active,
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ];
            $this->updateJournalTree($journal->id, $updateData);
            $journal->refresh();
            return ResponseWithSuccessData($lang, new JournalResource($journal), 1);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        // }
    }

    private function updateJournalTree($journalId, array $updateData)
    {
        $allIds = [$journalId];
        $this->collectDescendantIds($journalId, $allIds);
        Journal::whereIn('id', $allIds)->update($updateData);
    }

    private function collectDescendantIds($parentId, array &$allIds)
    {
        $children = Journal::where('parent_id', $parentId)->pluck('id');
        foreach ($children as $childId) {
            $allIds[] = $childId;
            $this->collectDescendantIds($childId, $allIds);
        }
    }

    public function delete(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = Journal::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            // if($data->level == 1){
            //     return respondError(($lang == 'en'? 'primary account': 'حساب رئيسى'), 404, $lang == 'en'? 'This is a primary account item that cannot be deleted.': 'هذا العنصر حساب رئيسى لا يمكن حذفه');
            // }

            if(count($data->children) > 0){
                return respondError(($lang == 'en'? 'Has linked journals': 'لديه حسابات مرتبطة'), 404, $lang == 'en'? 'This item has linked journals. Delete them first before deleting this item.': 'هذا العنصر لديه حسابات مرتبطة به .. احذفها أولاً ثم قم بالحذف');
            }

            if(count($data->journalEntryDetails) > 0){
                return respondError(($lang == 'en'? 'Has journal entery': 'لديه معاملات'), 404, $lang == 'en'? 'This item has journal entery. Delete them first before deleting this item.': 'هذا العنصر لديه معاملات مرتبطة به .. احذفها أولاً ثم قم بالحذف');
            }

            $data->deleted_by = authActionSave()['by'];
            $data->deleted_by_type = authActionSave()['type'];
            $data->save();
            $data->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    // public function account_statement(Request $request)
    // {
    //     $lang = request()->header('lang', 'ar');
    //     $parentId = $request->id;
        // $accounts = DB::select("
        //     WITH RECURSIVE journal_tree AS (
        //         SELECT id, parent_id, name_ar, name_en, code, debit, credit, balance
        //         FROM journals
        //         WHERE id = ?

        //         UNION ALL

        //         -- (Children / Descendants)
        //         SELECT j.id, j.parent_id, j.name_ar, j.name_en, j.code, j.debit, j.credit, j.balance
        //         FROM journals j
        //         JOIN journal_tree jt ON j.parent_id = jt.id
        //     )

        //     -- children (Leaf Nodes)
        //     SELECT *
        //     FROM journal_tree
        //     WHERE id NOT IN (
        //         SELECT DISTINCT parent_id FROM journals WHERE parent_id IS NOT NULL
        //     );
        // ", [$parentId]);

    //     $query = [];
    //     foreach($accounts as $account){
    //         $entry_details = JournalEntryDetails::with([
    //             'customer:id,name',
    //             'vendor:id,name_ar,name_en',
    //             'costCenter:id,name_ar,name_en',
    //             'journalEntry',
    //             'createdBy'
    //         ])->where('journal_id', $account->id)

    //         ->when($request->filled('from_date'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->whereDate('date', '>=', $request->from_date)
    //         ))
    //         ->when($request->filled('to_date'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->whereDate('date', '<=', $request->to_date)
    //         ))
    //         ->when($request->filled('journal_entry_numner'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->where('journal_entry_numner', $request->journal_entry_numner)
    //         ))
    //         ->when($request->filled('journal_entry_department_id'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->where('journal_entry_department_id', $request->journal_entry_department_id)
    //         ))
    //         ->when($request->filled('created_by'), fn($q) => $q->where('created_by', $request->created_by))
    //         ->when($request->filled('cost_center_id'), fn($q) => $q->where('cost_center_id', $request->cost_center_id))
    //         ->get();

    //         $entry_details = $entry_details->map(function ($detail) use($lang){
    //             return [
    //                 'id'                  => $detail->id,
    //                 'journal_id'          => $detail->journal_id,
    //                 'account_id'          => $detail->account_id,
    //                 'debit'               => $detail->debit,
    //                 'credit'              => $detail->credit,
    //                 'balance'             => $detail->credit - $detail->debit,
    //                 'description'         => $detail->description,
    //                 'customer'            => $detail->customer?->name,
    //                 'vendor'              => $detail->vendor?->name,
    //                 'cost_center'         => $detail->costCenter?->name,
    //                 'date'                => $detail->journalEntry?->date,
    //                 'journal_entry_number'=> $detail->journalEntry?->journal_entry_numner,
    //                 'department_id'       => $detail->journalEntry?->journalEntryDepartment?->name,
    //                 'journal_entry_departments'   => $detail->journalEntry?->journalEntryDepartment?->name,
    //                 'created_by'          => $detail->createdBy?->first_name.' '.$detail->createdBy?->last_name,
    //             ];
    //         });

    //         $query[] = [
    //             'id' => $account->id,
    //             'name' => $lang == "ar" ? $account->name_ar : $account->name_en,
    //             'debit' => $account->debit,
    //             'credit' => $account->credit,
    //             'balance' => $account->balance,
    //             'entry_details' => $entry_details,
    //         ];
    //     }

    //     return $query;
    // }



    // 4-12-2025
    // public function account_statement(Request $request)
    // {
    //     $lang = request()->header('lang', 'ar');
    //     $employee = auth('employee')->user();
    //     $facility_id = $employee->employeeFacility->facility_id;

    //     $from = $request->from_date ?? now()->startOfYear()->format('Y-m-d');
    //     $to   = $request->to_date   ?? now()->endOfYear()->format('Y-m-d');

    //     // $parentId = $request->id;
    //     if ($request->filled('journal_id')) {
    //         $parentId = $request->journal_id;
    //     }else{
    //         $parentId = $request->id;
    //     }
    //     $accounts = $this->getLeafAccountsUnder($parentId);


    //     $parent = Journal::where('id', $parentId)->first();
    //     $opening = JournalEntryDetails::where('journal_id', $parentId)
    //         ->whereHas('journalEntry', function ($q) use ($from) {
    //             $q->where('status', 'posted')
    //             ->whereDate('date', '<', $from);
    //         })
    //         ->selectRaw('SUM(debit) as opening_debit, SUM(credit) as opening_credit')
    //         ->first();

    //     $opening_debit  = $opening->opening_debit ?? 0;
    //     $opening_credit = $opening->opening_credit ?? 0;

    //     $all_opening_balance = $parent->account_type === 'debit'
    //         ? ($opening_debit - $opening_credit)
    //         : ($opening_credit - $opening_debit);

    //     $all_movement_balance = $all_closing_balance = 0;
    //     $query = [];
    //     foreach($accounts as $account){
    //         $entry_details = JournalEntryDetails::with([
    //             'customer:id,name',
    //             'vendor:id,name_ar,name_en',
    //             'costCenter:id,name_ar,name_en',
    //             'journalEntry',
    //             'createdBy'
    //         ])->where('journal_id', $account->id)
    //         ->when($request->filled('from_date'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->whereDate('date', '>=', $from)
    //         ))
    //         ->when($request->filled('to_date'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->whereDate('date', '<=', $to)
    //         ))
    //         ->when($request->filled('journal_entry_numner'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->where('journal_entry_numner', $request->journal_entry_numner)
    //         ))
    //         ->when($request->filled('journal_entry_department_id'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
    //             $d->where('journal_entry_department_id', $request->journal_entry_department_id)
    //         ))
    //         ->when($request->filled('created_by'), fn($q) => $q->where('created_by', $request->created_by))
    //         ->when($request->filled('cost_center_id'), fn($q) => $q->where('cost_center_id', $request->cost_center_id))
    //         ->whereHas('journalEntry', fn($d) =>
    //             $d->where('status', 'posted')
    //         )
    //         ->get();

    //         $movement_debit  = $entry_details->sum('debit');
    //         $movement_credit = $entry_details->sum('credit');

    //         $movement_balance = $account->account_type === 'debit'
    //             ? ($movement_debit - $movement_credit)
    //             : ($movement_credit - $movement_debit);

    //         $closing_balance = $all_opening_balance + $movement_balance;

    //         $all_movement_balance  += $movement_balance;
    //         $all_closing_balance   += $closing_balance;

    //         $entry_details = $entry_details->map(function ($detail) use($lang, $account, $from, $to){

    //             return [
    //                 'id'                  => $detail->id,
    //                 'journal_id'          => $detail->journal_id,
    //                 // 'name'                => $lang == "ar" ? $detail->journal?->name_ar : $detail->journal?->name_en,
    //                 // 'account_id'          => $detail->account_id,
    //                 'debit'               => $detail->debit,
    //                 'credit'              => $detail->credit,
    //                 'balance'             => $account->account_type == "credit" ? ($detail->credit - $detail->debit) : ($detail->debit - $detail->credit),
    //                 'description'         => $detail->description,
    //                 'customer'            => $detail->customer?->name,
    //                 'vendor'              => $detail->vendor?->name,
    //                 'cost_center'         => $detail->costCenter?->name,
    //                 'date'                => $detail->journalEntry?->date,
    //                 'journal_entry_id'    => $detail->journal_entry_id,
    //                 'journal_entry_number'=> $detail->journalEntry?->journal_entry_numner,
    //                 'department_id'       => $detail->journalEntry?->journalEntryDepartment?->name,
    //                 'journal_entry_departments'   => $detail->journalEntry?->journalEntryDepartment?->name,
    //                 'created_by'          => $detail->createdBy?->first_name.' '.$detail->createdBy?->last_name,
    //             ];

    //         });

    //         $query['account'][] = [
    //             'id' => $account->id,
    //             'name' => $lang == "ar" ? $account->name_ar : $account->name_en,
    //             'debit' => $account->debit,
    //             'credit' => $account->credit,
    //             'balance' => $account->balance,
    //             'entry_details' => $entry_details,
    //         ];
    //     }
    //     $query['balance'] = [
    //         'all_opening_balance' => $all_opening_balance,
    //         'all_movement_balance' => $all_movement_balance,
    //         'all_closing_balance' => $all_closing_balance
    //     ];

    //     return $query;
    // }

    public function account_statement(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        $employee = auth('employee')->user();
        $facility_id = $employee->employeeFacility->facility_id;

        $from = $request->from_date ?? now()->startOfYear()->format('Y-m-d');
        $to   = $request->to_date   ?? now()->format('Y-m-d');

        $parentId = $request->filled('journal_id') ? $request->journal_id : $request->id;
        $leafAccounts = $this->getLeafAccountsUnder($parentId);

        $accountsData = [];
        $total_opening_balance = $total_movement_balance = $total_closing_balance = 0;

        foreach ($leafAccounts as $account) {

            $opening = JournalEntryDetails::where('journal_id', $account->id)
                ->whereHas('journalEntry', fn($q) => $q->where('status', 'posted')->whereDate('date', '<', $from))
                ->selectRaw('COALESCE(SUM(debit), 0) as opening_debit, COALESCE(SUM(credit), 0) as opening_credit')
                ->first();

            $opening_balance = $account->account_type === 'debit'
                ? ($opening->opening_debit - $opening->opening_credit)
                : ($opening->opening_credit - $opening->opening_debit);

            $details = JournalEntryDetails::with([
                'customer:id,name',
                'vendor:id,name_ar,name_en',
                'costCenter:id,name_ar,name_en',
                'journalEntry:id,date,journal_entry_numner,journal_entry_department_id',
                'journalEntry.journalEntryDepartment:id,name_en,name_ar',
                'createdBy:id,first_name,last_name'
            ])
            ->where('journal_id', $account->id)
            ->whereHas('journalEntry', fn($q) =>
                $q->where('status', 'posted')
                ->whereBetween('date', [$from, $to])
            )
            ->when($request->filled('journal_entry_numner'), fn($q) => $q->whereHas('journalEntry', fn($d) => $d->where('journal_entry_numner', $request->journal_entry_numner)))
            ->when($request->filled('journal_entry_department_id'), fn($q) => $q->whereHas('journalEntry', fn($d) => $d->where('journal_entry_department_id', $request->journal_entry_department_id)))
            ->when($request->filled('created_by'), fn($q) => $q->where('created_by', $request->created_by))
            ->when($request->filled('cost_center_id'), fn($q) => $q->where('cost_center_id', $request->cost_center_id))
            ->get();

            $total_debit  = $details->sum('debit');
            $total_credit = $details->sum('credit');

            $movement_balance = $account->account_type === 'debit'
                ? ($total_debit - $total_credit)
                : ($total_credit - $total_debit);

            $closing_balance = $opening_balance + $movement_balance;

            $total_opening_balance   += $opening_balance;
            $total_movement_balance  += $movement_balance;
            $total_closing_balance   += $closing_balance;

            $entry_details = $details->map(function ($detail) use ($lang, $account) {
                $je = $detail->journalEntry;
                $dept = $je?->journalEntryDepartment;

                return [
                    'id'                     => $detail->id,
                    'journal_id'             => $detail->journal_id,
                    'debit'                  => number_format($detail->debit, 2, '.', ''),
                    'credit'                 => number_format($detail->credit, 2, '.', ''),
                    'balance'                => $account->account_type === 'credit'
                        ? ($detail->credit - $detail->debit)
                        : ($detail->debit - $detail->credit),
                    'description'            => $detail->description,
                    'customer'               => $detail->customer?->name,
                    'vendor'                 => $lang === 'ar' ? $detail->vendor?->name_ar : $detail->vendor?->name_en,
                    'cost_center'            => $lang === 'ar' ? $detail->costCenter?->name_ar : $detail->costCenter?->name_en,
                    'date'                   => $je?->date,
                    'journal_entry_id'       => $detail->journal_entry_id,
                    'journal_entry_number'   => $je?->journal_entry_numner,
                    'department_id'          => $je?->journalEntryDepartment?->id ?? null,
                    'journal_entry_departments' => $dept?->name ?? null,
                    'created_by'             => $detail->createdBy?->first_name . ' ' . $detail->createdBy?->last_name,
                ];
            })->values()->all();

            $accountsData[] = [
                'id'       => $account->id,
                'code'     => $account->code,
                'name'     => $lang === 'ar' ? $account->name_ar : $account->name_en,
                'debit'    => number_format($total_debit, 2, '.', ''),
                'credit'   => number_format($total_credit, 2, '.', ''),
                'balance'  => number_format($closing_balance, 2, '.', ''),
                'entry_details' => $entry_details
            ];
        }

        return [
            'account' => $accountsData,
            'balance' => [
                'all_opening_balance'  => number_format($total_opening_balance, 2, '.', ''),
                'all_movement_balance' => number_format($total_movement_balance, 2, '.', ''),
                'all_closing_balance'  => number_format($total_closing_balance, 2, '.', ''),
            ]
        ];
    }

    public function chartAccount(Request $request)
    {
        $lang = $request->header('lang', 'ar');
        if ($request->filled('journal_id')) {
            $parentId = $request->journal_id;
        }else{
            $parentId = $request->id;
        }

        $journal = Journal::where('id', $parentId)->first();

        $accounts = $this->getLeafAccountsUnder($parentId);
        $year = $request->filled('year') ? (int)$request->year : now()->year;

        if ($request->filled('from_date')) {
            $fromDate = Carbon::parse($request->from_date)->startOfDay();
        }else{
            $fromDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
        }

        if ($request->filled('to_date')) {
            $toDate   = Carbon::parse($request->to_date)->endOfDay();
        }else{
            $toDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
        }

        $entryDetails = JournalEntryDetails::whereIn('journal_id', $accounts->pluck('id'))
            // ->whereHas('journalEntry', fn($q) => $q->whereBetween('date', [$fromDate, $toDate]))
            ->whereHas('journalEntry', function ($q) use ($fromDate, $toDate) {
                $q->whereBetween('date', [$fromDate, $toDate])
                  ->where('status', 'posted');
            })
            ->get();

        $monthlyTransactions = $entryDetails->groupBy(function ($item) {
            return $item->journalEntry?->date
                ? Carbon::parse($item->journalEntry->date)->format('Y-m')
                : null;
        })->mapWithKeys(function ($items, $monthKey) {
            if (!$monthKey) return [];

            return [
                $monthKey => [
                    'total_debit'  => $items->sum('debit'),
                    'total_credit' => $items->sum('credit'),
                ]
            ];
        });

        $allMonths = collect();
        for ($m = 1; $m <= 12; $m++) {
            $date = Carbon::createFromDate($year, $m, 1);
            $key  = $date->format('Y-m');

            $data = $monthlyTransactions->get($key, [
                'total_debit'  => 0,
                'total_credit' => 0,
            ]);

            $allMonths->push([
                'month_year'     => $key,
                'month_number'   => $m,
                'month_name_ar'  => $date->translatedFormat('F Y'),
                'month_name_en'  => $date->format('F Y'),
                'total_debit'    => $data['total_debit']  + 0,
                'total_credit'   => $data['total_credit'] + 0,
                // 'net_movement'   => $data['total_credit'] - $data['total_debit'],
                'net_movement'   => $journal->account_type == "credit" ? ($data['total_credit'] - $data['total_debit']) : ($data['total_debit'] - $data['total_credit']),
            ]);
        }

        $grandDebit  = $allMonths->sum('total_debit');
        $grandCredit = $allMonths->sum('total_credit');
        // $grandNet    = $grandCredit - $grandDebit;
        $grandNet = $journal->account_type == "credit" ? ($grandCredit - $grandDebit) : ($grandDebit - $grandCredit);
        return [
            'journal_id'        => $parentId,
            'journal_name'      => $lang == "ar" ? $journal->name_ar : $journal->name_an,
            'type'              => __("finance/general.".$journal->type),
            'account_type'      => __("finance/general.".$journal->account_type),
            'year'              => $year,
            'months'            => $allMonths->values(),
            'total_debit'       => $grandDebit,
            'total_credit'      => $grandCredit,
            'net_balance'       => $grandNet,
        ];
    }

    public function getLeafAccountsUnder($parentId)
    {
        $descendants = collect([$parentId]);
        $children = Journal::whereIn('parent_id', $descendants)->pluck('id');
        while ($children->isNotEmpty()) {
            $descendants = $descendants->merge($children);
            $children = Journal::whereIn('parent_id', $children)->pluck('id');
        }

        return Journal::whereIn('id', $descendants)
            ->whereDoesntHave('children')
            ->orderBy('code')
            ->get();
    }

    // public function trialBalance(Request $request)
    // {
    //     $from = $request->from_date ?? now()->startOfYear()->format('Y-m-d');
    //     $to   = $request->to_date   ?? now()->endOfYear()->format('Y-m-d');
    //     $facilityId = $request->facility_id;

    //     $journals = Journal::with(['journalEntryDetails.journalEntry' => function ($q) {
    //         $q->where('status', 'posted');
    //     }])
    //     ->where('facility_id', $facilityId)
    //     ->orderBy('code')
    //     ->get();

    //     $processed = $journals->map(function ($account) use ($from, $to) {
    //         $details = $account->journalEntryDetails;

    //         $opening_debit  = $details->where('journalEntry.date', '<', $from)->sum('debit');
    //         $opening_credit = $details->where('journalEntry.date', '<', $from)->sum('credit');

    //         $period_debit  = $details->whereBetween('journalEntry.date', [$from, $to])->sum('debit');
    //         $period_credit = $details->whereBetween('journalEntry.date', [$from, $to])->sum('credit');

    //         $closing_balance = ($opening_debit - $opening_credit) + ($period_debit - $period_credit);

    //         return [
    //             'id'              => $account->id,
    //             'parent_id'       => $account->parent_id,
    //             'code'            => $account->code,
    //             'name_ar'         => $account->name_ar,
    //             'name_en'         => $account->name_en ?? $account->name_ar,
    //             'level'           => $account->level ?? 1,

    //             'opening_debit'   => $opening_debit,
    //             'opening_credit'  => $opening_credit,
    //             'period_debit'    => $period_debit,
    //             'period_credit'   => $period_credit,
    //             'closing_balance' => $closing_balance,

    //             'debit'           => $closing_balance >= 0 ? $closing_balance : 0,
    //             'credit'          => $closing_balance < 0 ? abs($closing_balance) : 0,

    //             'children'        => [],
    //             'has_children'    => $account->children()->exists(),
    //         ];
    //     });

    //     $tree = [];
    //     $lookup = [];

    //     foreach ($processed as $node) {
    //         $lookup[$node['id']] = $node;
    //     }

    //     foreach ($processed as $node) {
    //         if ($node['parent_id'] && isset($lookup[$node['parent_id']])) {
    //             $lookup[$node['parent_id']]['children'][] = &$lookup[$node['id']];

    //             $parent = &$lookup[$node['parent_id']];
    //             $parent['opening_debit']   += $node['opening_debit'];
    //             $parent['opening_credit']  += $node['opening_credit'];
    //             $parent['period_debit']    += $node['period_debit'];
    //             $parent['period_credit']   += $node['period_credit'];
    //             $parent['closing_balance'] += $node['closing_balance'];
    //             $parent['debit']           = $parent['closing_balance'] >= 0 ? $parent['closing_balance'] : 0;
    //             $parent['credit']          = $parent['closing_balance'] < 0 ? abs($parent['closing_balance']) : 0;
    //         } else {
    //             $tree[] = &$lookup[$node['id']];
    //         }
    //     }

    //     $totals = [
    //         'total_opening_debit'  => $processed->sum('opening_debit'),
    //         'total_opening_credit' => $processed->sum('opening_credit'),
    //         'total_period_debit'   => $processed->sum('period_debit'),
    //         'total_period_credit'  => $processed->sum('period_credit'),
    //         'total_closing_debit'  => $processed->sum(fn($i) => $i['debit']),
    //         'total_closing_credit' => $processed->sum(fn($i) => $i['credit']),
    //     ];

    //     return response()->json([
    //         'from_date' => $from,
    //         'to_date'   => $to,
    //         'data'      => $tree,
    //         'totals'    => $totals,
    //         'generated_at' => now()->toDateTimeString(),
    //     ]);
    // }

    // public function trialBalance(Request $request)
    // {
    //     $from = $request->from_date ?? now()->startOfYear()->format('Y-m-d');
    //     $to   = $request->to_date   ?? now()->endOfYear()->format('Y-m-d');
    //     $employee = auth('employee')->user();
    //     $facilityId = $employee->employeeFacility->facility_id;

    //     $accounts = Journal::with(['journalEntryDetails.journalEntry' => function ($q) {
    //             $q->where('status', 'posted');
    //         }])
    //         ->where('facility_id', $facilityId)
    //         ->when($request->filled('name'), function ($q) use ($request) {
    //             $name = $request->name;
    //             return $q->where(function ($query) use ($name) {
    //                 $query->where('name_en', 'LIKE', "%{$name}%")
    //                     ->orWhere('name_ar', 'LIKE', "%{$name}%")
    //                     ->orWhere('code', 'LIKE', "%{$name}%");
    //             });
    //         })
    //         ->when($request->filled('journal_id'), fn($q) => $q->where('id', $request->journal_id))
    //         ->when($request->filled('level'), fn($q) => $q->where('level', $request->level))
    //         ->orderBy('code')
    //         ->get();

    //     $processed = $accounts->map(function ($account) use ($from, $to) {
    //         $details = $account->journalEntryDetails->filter(fn($d) => $d->journalEntry?->status === 'posted');

    //         $opening_debit  = $details->where('journalEntry.date', '<', $from)->sum('debit');
    //         $opening_credit = $details->where('journalEntry.date', '<', $from)->sum('credit');

    //         $opening_signed = $account->account_type == "debit"
    //             ? ($opening_debit - $opening_credit)
    //             : ($opening_credit - $opening_debit);

    //         $movement_debit  = $details->whereBetween('journalEntry.date', [$from, $to])->sum('debit');
    //         $movement_credit = $details->whereBetween('journalEntry.date', [$from, $to])->sum('credit');

    //         $movement_signed = $account->account_type == "debit"
    //             ? ($movement_debit - $movement_credit)
    //             : ($movement_credit - $movement_debit);

    //         $closing_signed = $opening_signed + $movement_signed;

    //         return [
    //             'id' => $account->id,
    //             'parent_id' => $account->parent_id,
    //             'code' => $account->code,
    //             'name' => $account->name,
    //             'level' => $account->level ?? 1,

    //             'opening_signed' => $opening_signed,
    //             'movement_signed' => $movement_signed,
    //             'closing_signed' => $closing_signed,

    //             'children' => [],
    //             'has_children' => $account->children()->exists(),
    //             'account_type' => $account->account_type,
    //         ];
    //     });

    //     $tree = [];
    //     $lookup = $processed->keyBy('id')->toArray();

    //     foreach ($processed as $node) {
    //         $nodeRef = &$lookup[$node['id']];

    //         if ($node['parent_id'] && isset($lookup[$node['parent_id']])) {
    //             $parentRef = &$lookup[$node['parent_id']];
    //             $parentRef['children'][] = &$nodeRef;

    //             $parentRef['opening_signed']  += $nodeRef['opening_signed'];
    //             $parentRef['movement_signed'] += $nodeRef['movement_signed'];
    //             $parentRef['closing_signed']  += $nodeRef['closing_signed'];
    //         } else {
    //             $tree[] = &$nodeRef;
    //         }
    //     }

    //     $convertSigned = function($signed, $account_type) {
    //         if ($signed >= 0) {
    //             if ($account_type === 'debit') {
    //                 return ['debit' => $signed, 'credit' => 0];
    //             } else {
    //                 return ['debit' => 0, 'credit' => $signed];
    //             }
    //         } else {
    //             $abs = abs($signed);
    //             if ($account_type === 'debit') {
    //                 return ['debit' => 0, 'credit' => $abs];
    //             } else {
    //                 return ['debit' => $abs, 'credit' => 0];
    //             }
    //         }
    //     };

    //     // foreach ($lookup as $id => &$node) {
    //     //     $op = $convertSigned($node['opening_signed'], $node['account_type']);
    //     //     $mv = $convertSigned($node['movement_signed'], $node['account_type']);
    //     //     $cl = $convertSigned($node['closing_signed'], $node['account_type']);

    //     //     $node['opening'] = ['debit' => $op['debit'], 'credit' => $op['credit']];
    //     //     $node['movement'] = ['debit' => $mv['debit'], 'credit' => $mv['credit']];
    //     //     $node['closing'] = ['debit' => $cl['debit'], 'credit' => $cl['credit']];
    //     // }

    //     // $total_opening_signed  = array_sum(array_column($lookup, 'opening_signed'));
    //     // $total_movement_signed = array_sum(array_column($lookup, 'movement_signed'));
    //     // $total_closing_signed  = array_sum(array_column($lookup, 'closing_signed'));

    //     // $totals = [
    //     //     'opening' => $convertSigned($total_opening_signed, 'debit'),
    //     //     'movement' => $convertSigned($total_movement_signed, 'debit'),
    //     //     'closing' => $convertSigned($total_closing_signed, 'debit'),
    //     // ];

    //     $total_opening_debit = $total_opening_credit = 0;
    //     $total_movement_debit = $total_movement_credit = 0;
    //     $total_closing_debit = $total_closing_credit = 0;

    //     foreach ($lookup as $id => &$node) {
    //         $op = $convertSigned($node['opening_signed'], $node['account_type']);
    //         $mv = $convertSigned($node['movement_signed'], $node['account_type']);
    //         $cl = $convertSigned($node['closing_signed'], $node['account_type']);

    //         $node['opening'] = ['debit' => $op['debit'], 'credit' => $op['credit']];
    //         $node['movement'] = ['debit' => $mv['debit'], 'credit' => $mv['credit']];
    //         $node['closing'] = ['debit' => $cl['debit'], 'credit' => $cl['credit']];

    //         // هنا الإجماليات الصحيحة
    //         $total_opening_debit  += $op['debit'];
    //         $total_opening_credit += $op['credit'];

    //         $total_movement_debit  += $mv['debit'];
    //         $total_movement_credit += $mv['credit'];

    //         $total_closing_debit  += $cl['debit'];
    //         $total_closing_credit += $cl['credit'];
    //     }

    //     $totals = [
    //         'opening'  => ['debit' => $total_opening_debit, 'credit' => $total_opening_credit],
    //         'movement' => ['debit' => $total_movement_debit, 'credit' => $total_movement_credit],
    //         'closing'  => ['debit' => $total_closing_debit, 'credit' => $total_closing_credit],
    //     ];


    //     return [
    //         'from_date' => $from,
    //         'to_date' => $to,
    //         'data' => array_values($tree),
    //         'totals' => $totals,
    //         'footer_has_error' => $totals['closing']['debit'] != $totals['closing']['credit'],
    //         'generated_at' => now()->toDateTimeString(),
    //     ];
    // }


    public function trialBalance_old_9_12_2025(Request $request)
    {
        $from = $request->from_date ?? now()->startOfYear()->format('Y-m-d');
        $to   = $request->to_date   ?? now()->format('Y-m-d');

        $employee   = auth('employee')->user();
        $facilityId = $employee->employeeFacility->facility_id;

        $accounts = Journal::with(['journalEntryDetails.journalEntry' => function ($q) {
            $q->where('status', 'posted');
        }])
        ->where('facility_id', $facilityId)
        ->when($request->filled('name'), function ($q) use ($request) {
            $search = $request->name;
            $q->where(function ($query) use ($search) {
                $query->where('name_ar', 'like', "%{$search}%")
                    ->orWhere('name_en', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        })
        ->when($request->filled('journal_id'), fn($q) => $q->where('id', $request->journal_id))
        ->when($request->filled('level'), fn($q) => $q->where('level', $request->level))
        ->orderBy('code')
        ->get();

        $toDebitCredit = function ($balance, $account_type) {
            if ($balance >= 0) {
                return $account_type === 'debit'
                    ? ['debit' => $balance, 'credit' => 0]
                    : ['debit' => 0, 'credit' => $balance];
            } else {
                $abs = abs($balance);
                return $account_type === 'debit'
                    ? ['debit' => 0, 'credit' => $abs]
                    : ['debit' => $abs, 'credit' => 0];
            }
        };

        $processed = $accounts->map(function ($account) use ($from, $to, $toDebitCredit) {
            $details = $account->journalEntryDetails
                ->where('journalEntry.status', 'posted');

            $opDebit  = $details->where('journalEntry.date', '<', $from)->sum('debit');
            $opCredit = $details->where('journalEntry.date', '<', $from)->sum('credit');
            $openingBalance = $account->account_type === 'debit'
                ? ($opDebit - $opCredit)
                : ($opCredit - $opDebit);

            $mvDebit  = $details->whereBetween('journalEntry.date', [$from, $to])->sum('debit');
            $mvCredit = $details->whereBetween('journalEntry.date', [$from, $to])->sum('credit');

            $closingBalance = $openingBalance + ($account->account_type === 'debit'
                ? ($mvDebit - $mvCredit)
                : ($mvCredit - $mvDebit));

            return [
                'id'              => $account->id,
                'parent_id'       => $account->parent_id,
                'code'            => $account->code,
                'name'            => $account->name_ar ?? $account->name_en ?? $account->name,
                'level'           => $account->level ?? 1,
                'account_type'    => $account->account_type,

                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,

                'movement_debit'  => $mvDebit,
                'movement_credit' => $mvCredit,

                'children'        => [],
                'has_children'    => $account->children()->exists(),
            ];
        });

        $tree   = [];
        $lookup = $processed->keyBy('id')->toArray();

        foreach ($processed as $node) {
            $nodeRef = &$lookup[$node['id']];

            if ($node['parent_id'] && isset($lookup[$node['parent_id']])) {
                $parentRef = &$lookup[$node['parent_id']];
                $parentRef['children'][] = &$nodeRef;

                $parentRef['opening_balance'] += $nodeRef['opening_balance'];
                $parentRef['closing_balance'] += $nodeRef['closing_balance'];
            } else {
                $tree[] = &$nodeRef;
            }
        }

        foreach ($lookup as &$node) {
            $node['opening']  = $toDebitCredit($node['opening_balance'], $node['account_type']);
            $node['closing']  = $toDebitCredit($node['closing_balance'], $node['account_type']);
            $node['movement'] = [
                'debit'  => $node['movement_debit'],
                'credit' => $node['movement_credit']
            ];

            unset($node['opening_balance'], $node['closing_balance'], $node['movement_debit'], $node['movement_credit']);
        }

        // $totals = [
        //     'opening'  => [
        //         'debit'  => collect($lookup)->sum('opening.debit'),
        //         'credit' => collect($lookup)->sum('opening.credit'),
        //     ],
        //     'movement' => [
        //         'debit'  => collect($lookup)->sum('movement.debit'),
        //         'credit' => collect($lookup)->sum('movement.credit'),
        //     ],
        //     'closing'  => [
        //         'debit'  => collect($lookup)->sum('closing.debit'),
        //         'credit' => collect($lookup)->sum('closing.credit'),
        //     ],
        // ];

        $mainAccounts = collect($tree)->filter(fn($account) => $account['level'] == 1);
        $totals = [
            'opening'  => [
                'debit'  => $mainAccounts->sum('opening.debit'),
                'credit' => $mainAccounts->sum('opening.credit'),
            ],
            'movement' => [
                'debit'  => collect($lookup)->sum('movement.debit'),
                'credit' => collect($lookup)->sum('movement.credit'),
            ],
            'closing'  => [
                'debit'  => $mainAccounts->sum('closing.debit'),
                'credit' => $mainAccounts->sum('closing.credit'),
            ],
        ];

        return [
                'from_date'        => $from,
                'to_date'          => $to,
                'data'             => array_values($tree),
                'totals'           => $totals,
                'footer_has_error' =>
                    abs($totals['movement']['debit'] - $totals['movement']['credit']) > 0.01 ||
                    abs($totals['closing']['debit']  - $totals['closing']['credit'])  > 0.01,
                'generated_at'     => now()->toDateTimeString(),
            ];
    }

    public function trialBalance(Request $request)
    {
        $from = $request->from_date ?? now()->startOfYear()->format('Y-m-d');
        $to   = $request->to_date   ?? now()->endOfYear()->format('Y-m-d');

        $employee   = auth('employee')->user();
        $facilityId = $employee->employeeFacility->facility_id;

        $orphanedDetails = DB::table('journal_entry_details as det')
            ->leftJoin('journal_entries as ent', 'det.journal_entry_id', '=', 'ent.id')
            ->where('ent.facility_id', $facilityId)
            ->where(function($q) {
                $q->whereNull('ent.id')
                ->orWhere('ent.status', '!=', 'posted')
                ->orWhereNotNull('ent.deleted_at');
            })
            ->count();
        if ($orphanedDetails > 0) {
            Log::warning("وجد $orphanedDetails حركة مشبوهة في منشأة $facilityId");
        }

        $accounts = Journal::where('facility_id', $facilityId)
            ->when($request->filled('name'), fn($q) => $q->where('name_ar', 'like', "%{$request->name}%")
                ->orWhere('name_en', 'like', "%{$request->name}%")
                ->orWhere('code', 'like', "%{$request->name}%"))
            ->when($request->filled('journal_id'), fn($q) => $q->where('id', $request->journal_id))
            ->when($request->filled('level'), fn($q) => $q->where('level', $request->level))
            ->orderBy('code')
            ->get();

        $nodes = [];

        foreach ($accounts as $account) {
            $details = DB::table('journal_entry_details')
                ->join('journal_entries', 'journal_entry_details.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_details.journal_id', $account->id)
                ->where('journal_entries.facility_id', $facilityId)
                ->where('journal_entries.status', 'posted')
                ->whereNull('journal_entries.deleted_at')           // لا قيود محذوفة
                ->when(
                    DB::getSchemaBuilder()->hasColumn('journal_entry_details', 'deleted_at'),
                    fn($q) => $q->whereNull('journal_entry_details.deleted_at')
                )
                ->select('journal_entry_details.debit', 'journal_entry_details.credit', 'journal_entries.date')
                ->get();

            $op_debit  = $details->where('date', '<', $from)->sum('debit');
            $op_credit = $details->where('date', '<', $from)->sum('credit');

            $mv_debit  = $details->whereBetween('date', [$from, $to])->sum('debit');
            $mv_credit = $details->whereBetween('date', [$from, $to])->sum('credit');

            $closing_debit_balance  = ($op_debit  + $mv_debit)  - ($op_credit + $mv_credit);
            $closing_credit_balance = ($op_credit + $mv_credit) - ($op_debit  + $mv_debit);

            $closing_debit  = $closing_debit_balance  > 0 ? $closing_debit_balance  : 0;
            $closing_credit = $closing_credit_balance > 0 ? $closing_credit_balance : 0;

            $nodes[$account->id] = [
                'id'            => $account->id,
                'parent_id'     => $account->parent_id,
                'code'          => $account->code,
                'name'          => $account->name_ar ?? $account->name_en ?? $account->name,
                'level'         => $account->level ?? 1,
                'account_type'  => $account->account_type,

                'opening_debit'   => $op_debit,
                'opening_credit'  => $op_credit,
                'movement_debit'  => $mv_debit,
                'movement_credit' => $mv_credit,
                'closing_debit'   => $closing_debit,
                'closing_credit'  => $closing_credit,

                'children'      => [],
                'has_children'  => $account->children()->exists(),
                'has_transaction'  => $account->journalEntryDetails()->exists(),
            ];
        }

        $tree = [];
        foreach ($nodes as $id => &$node) {
            if ($node['parent_id'] && isset($nodes[$node['parent_id']])) {
                $nodes[$node['parent_id']]['children'][] = &$node;
            } else {
                $tree[] = &$node;
            }
        }
        unset($node);

        function aggregate(&$node)
        {
            if (empty($node['children'])) return;

            foreach ($node['children'] as &$child) {
                aggregate($child);

                $node['opening_debit']   += $child['opening_debit'];
                $node['opening_credit']  += $child['opening_credit'];
                $node['movement_debit']  += $child['movement_debit'];
                $node['movement_credit'] += $child['movement_credit'];

                $total_debit  = $node['opening_debit']  + $node['movement_debit'];
                $total_credit = $node['opening_credit'] + $node['movement_credit'];

                $node['closing_debit']  = $total_debit  > $total_credit ? $total_debit  - $total_credit : 0;
                $node['closing_credit'] = $total_credit > $total_debit  ? $total_credit - $total_debit  : 0;
            }
            unset($child);
        }

        foreach ($tree as &$root) {
            aggregate($root);
        }
        unset($root);

        $totals = [
            'opening'  => ['debit' => 0, 'credit' => 0],
            'movement' => ['debit' => 0, 'credit' => 0],
            'closing'  => ['debit' => 0, 'credit' => 0],
        ];

        $format = function (&$nodes) use (&$format, &$totals) {
            foreach ($nodes as &$node) {
                $node['opening']  = ['debit' => $node['opening_debit'],   'credit' => $node['opening_credit']];
                $node['movement'] = ['debit' => $node['movement_debit'],  'credit' => $node['movement_credit']];
                $node['closing']  = ['debit' => $node['closing_debit'],   'credit' => $node['closing_credit']];

                if ($node['level'] == 1) {
                    $totals['opening']['debit']   += $node['opening_debit'];
                    $totals['opening']['credit']  += $node['opening_credit'];
                    $totals['movement']['debit']  += $node['movement_debit'];
                    $totals['movement']['credit'] += $node['movement_credit'];
                    $totals['closing']['debit']   += $node['closing_debit'];
                    $totals['closing']['credit']  += $node['closing_credit'];
                }

                unset(
                    $node['opening_debit'], $node['opening_credit'],
                    $node['movement_debit'], $node['movement_credit'],
                    $node['closing_debit'], $node['closing_credit']
                );

                if (!empty($node['children'])) {
                    $format($node['children']);
                }
            }
            unset($node);
        };

        $format($tree);

        $hasError = abs($totals['closing']['debit'] - $totals['closing']['credit']) > 0.01;

        return [
            'from_date'        => $from,
            'to_date'          => $to,
            'data'             => $tree,
            'totals'           => $totals,
            'footer_has_error' => $hasError,
            'generated_at'     => now()->toDateTimeString(),
        ];
    }

    public function exportJournals(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
            $employee   = auth('employee')->user();
            $facilityId = $employee->employeeFacility->facility_id;

            $fileName = 'journals_template' . now()->format('Y_m_d_H_i_s') . '.xlsx';
            return Excel::download(new JournalsExport($facilityId), $fileName);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        // }
    }

    public function importJournals(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
            $validator = Validator::make(request()->all(), [
                'file' => 'required|mimes:xlsx,xls'
            ], [
                'file.required' => $lang == 'en' ? 'File is required' : 'الملف مطلوبة',
            ]);

            if ($validator->fails()) {
                return respondError(
                    $lang == 'en' ? 'Validation error' : 'خطأ في التحقق',
                    400,
                    $validator->errors()->all()
                );
            }
            
            $employee   = auth('employee')->user();
            $facilityId = $employee->employeeFacility->facility_id;

            $import = new JournalsImport($facilityId,authActionSave()['by'],authActionSave()['type']);
            Excel::import($import, $request->file('file'));

            $import->fixParentsAfterImport();

            foreach ($import->failures() as $failure) {
                Log::warning('Excel validation error', [
                    'row'       => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'errors'    => $failure->errors(),
                    'values'    => $failure->values(),
                ]);
            }
            return ResponseWithSuccessData(request()->header('lang', 'ar'), "تم استيراد شجرة الحسابات بنجاح", 1);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        // }
    }
}
