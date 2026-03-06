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
use App\Models\Branch;
use App\Models\CompanyProfileSetting;
use App\Models\JournalEntryDetails;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;

class JournalService_old
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
        return $query;
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
                    'code' => $levelAndCode['code'],
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
            $result = [
                'name_ar'          => $data['name_ar'] ?? $journal->name_ar,
                'name_en'          => $data['name_en'] ?? $journal->name_en,
                'description_ar'   => $data['description_ar'] ?? $journal->description_ar,
                'type'             => $data['type'] ?? $journal->type,
                'account_type'     => $data['account_type'] ?? $journal->account_type,
                'parent_id'        => $data['parent_id'] ?? $journal->parent_id,
                'currency_id'      => $data['currency_id'] ?? $journal->currency_id,
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ];
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

            if($data->level == 1){
                return respondError(($lang == 'en'? 'primary account': 'حساب رئيسى'), 404, $lang == 'en'? 'This is a primary account item that cannot be deleted.': 'هذا العنصر حساب رئيسى لا يمكن حذفه');
            }

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



    public function account_statement(Request $request)
    {
        $lang = request()->header('lang', 'ar');
        $employee = auth('employee')->user();        
        $facility_id = $employee->employeeFacility->facility_id;

        // $parentId = $request->id;
        if ($request->filled('journal_id')) {
            $parentId = $request->journal_id;
        }else{
            $parentId = $request->id;
        }
        $accounts = $this->getLeafAccountsUnder($parentId);

        $query = [];
        foreach($accounts as $account){
            $entry_details = JournalEntryDetails::with([
                'customer:id,name',
                'vendor:id,name_ar,name_en',
                'costCenter:id,name_ar,name_en',
                'journalEntry',
                'createdBy'
            ])->where('journal_id', $account->id)

            ->when($request->filled('from_date'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
                $d->whereDate('date', '>=', $request->from_date)
            ))
            ->when($request->filled('to_date'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
                $d->whereDate('date', '<=', $request->to_date)
            ))
            ->when($request->filled('journal_entry_numner'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
                $d->where('journal_entry_numner', $request->journal_entry_numner)
            ))
            ->when($request->filled('journal_entry_department_id'), fn($q) => $q->whereHas('journalEntry', fn($d) =>
                $d->where('journal_entry_department_id', $request->journal_entry_department_id)
            ))
            ->when($request->filled('created_by'), fn($q) => $q->where('created_by', $request->created_by))
            ->when($request->filled('cost_center_id'), fn($q) => $q->where('cost_center_id', $request->cost_center_id))
            ->whereHas('journalEntry', fn($d) =>
                $d->where('status', 'posted')

            )
            ->get();

            $entry_details = $entry_details->map(function ($detail) use($lang, $account){
                return [
                    'id'                  => $detail->id,
                    'journal_id'          => $detail->journal_id,
                    // 'name'                => $lang == "ar" ? $detail->journal?->name_ar : $detail->journal?->name_en,
                    // 'account_id'          => $detail->account_id,
                    'debit'               => $detail->debit,
                    'credit'              => $detail->credit,
                    'balance'             => $account->account_type == "credit" ? ($detail->credit - $detail->debit) : ($detail->debit - $detail->credit),
                    'description'         => $detail->description,
                    'customer'            => $detail->customer?->name,
                    'vendor'              => $detail->vendor?->name,
                    'cost_center'         => $detail->costCenter?->name,
                    'date'                => $detail->journalEntry?->date,
                    'journal_entry_id'    => $detail->journal_entry_id,
                    'journal_entry_number'=> $detail->journalEntry?->journal_entry_numner,
                    'department_id'       => $detail->journalEntry?->journalEntryDepartment?->name,
                    'journal_entry_departments'   => $detail->journalEntry?->journalEntryDepartment?->name,
                    'created_by'          => $detail->createdBy?->first_name.' '.$detail->createdBy?->last_name,
                ];
            });

            $query[] = [
                'id' => $account->id,
                'name' => $lang == "ar" ? $account->name_ar : $account->name_en,
                'debit' => $account->debit,
                'credit' => $account->credit,
                'balance' => $account->balance,
                'entry_details' => $entry_details,
            ];
        }

        return $query;
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
    //     // $facilityId = $request->facility_id;
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
    //         // $details = $account->journalEntryDetails;
    //         $details = $account->journalEntryDetails->filter(fn($d) => $d->journalEntry?->status === 'posted');

    //         $opening_debit  = $details->where('journalEntry.date', '<', $from)->sum('debit');
    //         $opening_credit = $details->where('journalEntry.date', '<', $from)->sum('credit');
    //         $opening_balance = $account->account_type == "debit" ? ($opening_debit - $opening_credit) : ($opening_credit - $opening_debit);

    //         // if($account->account_type == "credit"){
    //         //     $opening_balance_debit = 0;
    //         //     $opening_balance_credit = $opening_credit - $opening_debit;
    //         //     $closing_balance_check = true;
    //         // }else{
    //         //     $opening_balance_debit = $opening_credit - $opening_debit;
    //         //     $opening_balance_credit = 0;
    //         //     $closing_balance_check = true;
    //         // }
    //         // $opening_balance_debit = $opening_debit;
    //         // $opening_balance_credit = $opening_credit;


    //         $movement_debit  = $details->whereBetween('journalEntry.date', [$from, $to])->sum('debit');
    //         $movement_credit = $details->whereBetween('journalEntry.date', [$from, $to])->sum('credit');
    //         $movement_balance = $account->account_type == "debit" ? ($movement_debit - $movement_credit) : ($movement_credit - $movement_debit);

    //         // $closing_balance = $opening_balance + ($movement_debit - $movement_credit);
    //         $closing_balance = $opening_balance + $movement_balance;
    //         $closing_balance_check = false;
    //         if($account->account_type == "debit"){
    //             if($closing_balance > 0){
    //                 $closing_debit = $closing_balance;
    //                 $closing_credit = 0;
    //                 $closing_balance_check = false;
    //             }else{
    //                 $closing_debit = 0;
    //                 $closing_credit = $closing_balance;
    //                 $closing_balance_check = true;
    //             }
    //         }

    //         if($account->account_type == "credit"){
    //             if($closing_balance > 0){
    //                 $closing_debit = 0;
    //                 $closing_credit = $closing_balance;
    //                 $closing_balance_check = false;
    //             }else{
    //                 $closing_debit = $closing_balance;
    //                 $closing_credit = 0;
    //                 $closing_balance_check = true;
    //             }
    //         }

    //         return [
    //             'id'         => $account->id,
    //             'parent_id'  => $account->parent_id,
    //             'code'       => $account->code,
    //             'name'       => $account->name,
    //             // 'name_ar'    => $account->name_ar,
    //             // 'name_en'    => $account->name_en ?? $account->name_ar,
    //             'level'      => $account->level ?? 1,
    //             'closing_balance_check'      => $closing_balance_check,

    //             'opening' => [
    //                 'debit'  => $opening_debit,
    //                 'credit' => $opening_credit,
    //             ],
    //             'movement' => [
    //                 'debit'  => $movement_debit,
    //                 'credit' => $movement_credit,
    //             ],
    //             // 'closing' => [
    //             //     'debit'  => $closing_balance >= 0 ? $closing_balance : 0,
    //             //     'credit' => $closing_balance < 0 ? $closing_balance : 0,
    //             // ],
    //             'closing' => [
    //                 'debit'  => $closing_debit,
    //                 'credit' => $closing_credit,
    //             ],

    //             'children'     => [],
    //             'has_children' => $account->children()->exists(),
    //         ];
    //     });

    //     $tree = [];
    //     $lookup = $processed->keyBy('id')->toArray();

    //     foreach ($processed as $node) {
    //         $nodeRef = &$lookup[$node['id']];

    //         if ($node['parent_id'] && isset($lookup[$node['parent_id']])) {
    //             $parentRef = &$lookup[$node['parent_id']];
    //             $parentRef['children'][] = &$nodeRef;

    //             $parentRef['opening']['debit']   += $node['opening']['debit'];
    //             $parentRef['opening']['credit']  += $node['opening']['credit'];
    //             $parentRef['movement']['debit']  += $node['movement']['debit'];
    //             $parentRef['movement']['credit'] += $node['movement']['credit'];
    //             $parentRef['closing']['debit']   += $node['closing']['debit'];
    //             $parentRef['closing']['credit']  += $node['closing']['credit'];
    //         } else {
    //             $tree[] = &$nodeRef;
    //         }
    //     }

    //     // $totals = [
    //     //     'total_opening_debit'   => $processed->sum('opening.debit'),
    //     //     'total_opening_credit'  => $processed->sum('opening.credit'),
    //     //     'total_movement_debit'  => $processed->sum('movement.debit'),
    //     //     'total_movement_credit' => $processed->sum('movement.credit'),
    //     //     'total_closing_debit'   => $processed->sum('closing.debit'),
    //     //     'total_closing_credit'  => $processed->sum('closing.credit'),
    //     // ];
    //     $totals = [
    //         'opening' => ['debit' => $processed->sum('opening.debit'), 'credit' => $processed->sum('opening.credit') ],
    //         'movement' => [ 'debit' => $processed->sum('movement.debit'), 'credit' => $processed->sum('movement.credit') ],
    //         'closing' => [ 'debit' => $processed->sum('closing.debit'), 'credit' => $processed->sum('closing.credit') ],
    //     ];

    //     return [
    //         'from_date'    => $from,
    //         'to_date'      => $to,
    //         'data'         => array_values($tree),
    //         'totals'       => $totals,
    //         'footer_has_error' => $processed->sum('closing.debit') != $processed->sum('closing.credit') ? true : false,
    //         'generated_at' => now()->toDateTimeString(),
    //     ];
    // }


    public function trialBalance(Request $request)
    {
        $from = $request->from_date ?? now()->startOfYear()->format('Y-m-d');
        $to   = $request->to_date   ?? now()->endOfYear()->format('Y-m-d');

        $employee   = auth('employee')->user();
        $facilityId = $employee->employeeFacility->facility_id;

        $accounts = Journal::with(['journalEntryDetails.journalEntry' => fn($q) => $q->where('status', 'posted')])
            ->where('facility_id', $facilityId)
            ->when($request->filled('name'), function ($q) use ($request) {
                $name = $request->name;
                $q->where(fn($query) => $query->where('name_en', 'like', "%{$name}%")
                    ->orWhere('name_ar', 'like', "%{$name}%")
                    ->orWhere('code', 'like', "%{$name}%"));
            })
            ->when($request->filled('journal_id'), fn($q) => $q->where('id', $request->journal_id))
            ->when($request->filled('level'), fn($q) => $q->where('level', $request->level))
            ->orderBy('code')
            ->get();

        $convertBalance = function ($balance, $type) {
            if ($balance >= 0) {
                return $type === 'debit'
                    ? ['debit' => $balance, 'credit' => 0]
                    : ['debit' => 0, 'credit' => $balance];
            } else {
                $abs = abs($balance);
                return $type === 'debit'
                    ? ['debit' => 0, 'credit' => $abs]
                    : ['debit' => $abs, 'credit' => 0];
            }
        };

        $processed = $accounts->map(function ($account) use ($from, $to, $convertBalance) {
            $details = $account->journalEntryDetails->where('journalEntry.status', 'posted');

            $opDebit  = $details->where('journalEntry.date', '<', $from)->sum('debit');
            $opCredit = $details->where('journalEntry.date', '<', $from)->sum('credit');
            $openingBalance = $account->account_type === 'debit'
                ? ($opDebit - $opCredit)
                : ($opCredit - $opDebit);

            $mvDebit  = $details->whereBetween('journalEntry.date', [$from, $to])->sum('debit');
            $mvCredit = $details->whereBetween('journalEntry.date', [$from, $to])->sum('credit');

            $movementBalance = $account->account_type === 'debit'
                ? ($mvDebit - $mvCredit)
                : ($mvCredit - $mvDebit);

            $closingBalance = $openingBalance + $movementBalance;

            return [
                'id'           => $account->id,
                'parent_id'    => $account->parent_id,
                'code'         => $account->code,
                'name'         => $account->name_ar ?? $account->name_en ?? $account->name,
                'level'        => $account->level ?? 1,
                'account_type' => $account->account_type,

                'opening_balance'  => $openingBalance,
                'closing_balance'  => $closingBalance,

                'movement_debit'   => $mvDebit,
                'movement_credit'  => $mvCredit,

                'children'     => [],
                'has_children' => $account->children()->exists(),
            ];
        });

        $tree = [];
        $lookup = $processed->keyBy('id')->toArray();

        foreach ($processed as $node) {
            $nodeRef = &$lookup[$node['id']];

            if ($node['parent_id'] && isset($lookup[$node['parent_id']])) {
                $parentRef = &$lookup[$node['parent_id']];
                $parentRef['children'][] = &$nodeRef;

                // نجمع الأرصدة الموقعة فقط
                $parentRef['opening_balance'] += $nodeRef['opening_balance'];
                $parentRef['closing_balance'] += $nodeRef['closing_balance'];
            } else {
                $tree[] = &$nodeRef;
            }
        }

        foreach ($lookup as &$node) {
            $op = $convertBalance($node['opening_balance'], $node['account_type']);
            $cl = $convertBalance($node['closing_balance'], $node['account_type']);

            $node['opening']  = $op;
            $node['closing']  = $cl;
            $node['movement'] = [
                'debit'  => $node['movement_debit'],
                'credit' => $node['movement_credit']
            ];

            unset($node['opening_balance'], $node['closing_balance'], $node['movement_debit'], $node['movement_credit']);
    }

    $mainAccounts = collect($tree)->filter(fn($account) => $account['level'] == 1);
    
    $totals = [
        'opening'  => [
            'debit'  => $mainAccounts->sum('opening.debit'),
            'credit' => $mainAccounts->sum('opening.credit'),
        ],
        'movement' => [
            'debit'  => $mainAccounts->sum('movement.debit'),
            'credit' => $mainAccounts->sum('movement.credit'),
        ],
        'closing'  => [
            'debit'  => $mainAccounts->sum('closing.debit'),
            'credit' => $mainAccounts->sum('closing.credit'),
        ],
    ];

    return [
        'from_date' => $from,
        'to_date'   => $to,
        'data'      => array_values($tree),
        'totals'    => $totals,
        'footer_has_error' => 
            abs($totals['closing']['debit'] - $totals['closing']['credit']) > 0.01,
        'generated_at' => now()->toDateTimeString(),
    ];
}


}
