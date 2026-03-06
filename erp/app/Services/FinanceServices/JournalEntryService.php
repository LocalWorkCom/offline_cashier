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
use App\Http\Resources\Finance\JournalEntryResource;
use App\Models\JournalEntry;
use App\Models\JournalEntryDetails;
use App\Models\Branch;
use App\Models\CompanyProfileSetting;
use App\Models\CostCenter;
use App\Models\Currency;
use Illuminate\Support\Facades\Storage;

use Google\Service\Datastream\Merge;

class JournalEntryService
{
    public function getAll(Request $request)
    {
        $employee = auth('employee')->user();
        $facility_id = $employee->employeeFacility->facility_id;

        $query = JournalEntry::where('facility_id', $facility_id)->with([
            'facility',
            'currency',
            'journalEntryDetails',
            'journalEntryDetails.journal',
            'journalEntryDetails.costCenter',
            'journalEntryDetails.customer',
            'journalEntryDetails.vendor'
        ])
        ->when($request->filled('name'), function ($q) use ($request) {
            $search = $request->name;
            $q->where(function ($query) use ($search) {
                $query->where('ledger_number', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('journalEntryDetails', function ($d) use ($search) {
                        $d->where('name', 'like', "%{$search}%")
                            ->orWhereHas('customer', fn($c) =>
                                $c->where('name', 'like', "%{$search}%")
                            )
                            ->orWhereHas('vendor', fn($v) =>
                                $v->where('name_ar', 'like', "%{$search}%")
                                ->orWhere('name_en', 'like', "%{$search}%")
                            );
                    });
            });
        })
        ->when($request->filled('account_type'), fn($q) => $q->where('account_type', $request->account_type))
        ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
        ->when($request->filled('cost_center_id'), fn($q) => $q->whereHas('journalEntryDetails', fn($d) =>
            $d->where('cost_center_id', $request->cost_center_id)
        ))
        ->when($request->filled('currency_id'), fn($q) => $q->where('currency_id', $request->currency_id))
        ->when($request->filled('created_by'), fn($q) => $q->where('created_by', $request->created_by))
        ->when($request->filled('from_date'), fn($q) => $q->whereDate('date', '>=', $request->from_date))
        ->when($request->filled('to_date'), fn($q) => $q->whereDate('date', '<=', $request->to_date))

        ->when($request->filled('ledger_number'), fn($q) => $q->where('ledger_number', 'like', "%{$request->ledger_number}%"))
        ->when($request->filled('journal_entry_number'), fn($q) => $q->where('journal_entry_number', 'like', "%{$request->journal_entry_number}%"));

        return $query;
    }

    public function show(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = JournalEntry::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            return ResponseWithSuccessData(request()->header('lang', 'ar'), new JournalEntryResource($data), 1);
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

            $data = JournalEntry::where('facility_id', $facility_id)->get();
            $journalEntries = $data->map(function ($journalEntry) use ($lang) {
                return[
                    'id' => $journalEntry->id,
                    'name' => $lang === 'ar' ? $journalEntry->name_ar : $journalEntry->name_en,
                    'code' => $journalEntry->code
                ];
            });

            return ResponseWithSuccessData(request()->header('lang', 'ar'), $journalEntries, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function add(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $data = $request->validated();
            
            $employee = auth('employee')->user();
            $facility_id = $employee->employeeFacility->facility_id;
            // return $this->generateJournalEntryNumner($facility_id);

            $journalEntry = JournalEntry::create([
                'facility_id'                 => $facility_id,
                'journal_entry_department_id' => $data['journal_entry_department_id'],
                'journal_entry_numner'        => $this->generateJournalEntryNumner($facility_id),
                'ledger_number'               => $data['ledger_number'],
                'account_type'                => $data['account_type'],
                'date'                        => $data['date'],
                'description'                 => $data['description'] ?? null,
                'currency_id'                 => $data['currency_id'] ?? null,
                'is_repeated'                 => $data['is_repeated'],
                'repeated_count'              => $data['repeated_count'] ?? null,
                'repeated_type'               => $data['repeated_type'] ?? null,
                'status'                      => $data['status'] ?? 'draft',
                'total_debit'                 => 0,
                'total_credit'                => 0,
                'current_total_debit'        => 0,
                'current_total_credit'       => 0,
                'created_by'                  => authActionSave()['by'],
                'created_by_type'             => authActionSave()['type'],
                'active'                      => 1,
            ]);

            $totalDebit = $totalCredit = 0;
            $current_total_debit = $current_total_credit = 0;
            $currencyDetails = Currency::with(['currencyExchange' => fn($q) =>
                    $q->where('is_active', 1)
                    ->where('facility_id', $facility_id)
                ])
                ->find($data['currency_id']);
            $exchangeValue = $currencyDetails?->currencyExchange[0]?->exchange_value ?? 1;
            foreach ($data['journal_entry_details'] as $detail) {
                $current_credit  = $detail['credit'] ?? 0;
                $current_debit = $detail['debit'] ?? 0;
                $debit  = ($detail['debit'] *  $exchangeValue) ?? 0;
                $credit = ($detail['credit'] *  $exchangeValue) ?? 0;

                $journalEntry->journalEntryDetails()->create([
                    'journal_id'       => $detail['journal_id'],
                    'name'             => $detail['name'] ?? null,
                    'customer_id'      => $detail['customer_id'] ?? null,
                    'vendor_id'        => $detail['vendor_id'] ?? null,
                    'cost_center_id'   => $detail['cost_center_id'] ?? null,
                    'debit'            => $debit,
                    'credit'           => $credit,
                    'current_credit'   => $current_credit,
                    'current_debit'    => $current_debit,
                    'description'      => $detail['description'] ?? null,
                    'created_by'       => authActionSave()['by'],
                    'created_by_type'  => authActionSave()['type'],
                ]);

                $totalDebit  += $debit;
                $totalCredit += $credit;
                $current_total_debit += $current_debit;
                $current_total_credit += $current_credit;
            }

            $journalEntry->update([
                'total_debit'  => $totalDebit,
                'total_credit' => $totalCredit,
                'current_total_debit'  => $current_total_debit,
                'current_total_credit' => $current_total_credit,
            ]);

            $journalEntry->refresh();

            if ($journalEntry->status === 'posted') {
                $this->postJournalEntry($journalEntry);
                $this->postCostCenter($journalEntry);
            }

            return $journalEntry->load('journalEntryDetails');
        });
    }

    public function edit(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            return DB::transaction(function () use ($request, $lang) {
                $data = $request->validated();

                $journalEntry = JournalEntry::find($request->id);
                if (!$journalEntry) {
                    return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
                }

                $journalEntry->update([
                    // 'facility_id'                 => $data['facility_id'],
                    'journal_entry_department_id' => $data['journal_entry_department_id'],
                    // 'journal_entry_number'        => $this->generateJournalEntryNumner($data['facility_id']),
                    'ledger_number'               => $data['ledger_number'],
                    'account_type'                => $data['account_type'],
                    'date'                        => $data['date'],
                    'description'                 => $data['description'] ?? null,
                    'currency_id'                 => $data['currency_id'] ?? null,
                    'is_repeated'                 => $data['is_repeated'],
                    'repeated_count'              => $data['repeated_count'] ?? null,
                    'repeated_type'               => $data['repeated_type'] ?? null,
                    'status'                      => $data['status'] ?? 'draft',
                    'total_debit'                 => 0,
                    'total_credit'                => 0,
                    'current_total_debit'        => 0,
                    'current_total_credit'       => 0,
                    'modified_by'                 => authActionSave()['by'],
                    'modified_by_type'            => authActionSave()['type'],
                    'active'                      => 1,
                ]);

                $entry_details_ids = $journalEntry->journalEntryDetails->pluck('id');

                $incomingDetailIds = array_column($data['journal_entry_details'], 'id');
                $incomingDetailIds = array_unique(array_filter($incomingDetailIds));

                $deleted_entry_details = $journalEntry->journalEntryDetails()
                    ->when(!empty($incomingDetailIds), fn($q) => $q->whereNotIn('id', $incomingDetailIds))
                    ->delete();

                $totalDebit = $totalCredit = 0;
                $current_total_debit = $current_total_credit = 0;
                $currencyDetails = Currency::with(['currencyExchange' => fn($q) =>
                        $q->where('is_active', 1)
                        ->where('facility_id', $data['facility_id'])
                    ])
                    ->find($data['currency_id']);
                $exchangeValue = $currencyDetails?->currencyExchange[0]?->exchange_value ?? 1;

                foreach ($data['journal_entry_details'] as $detail) {
                    $current_credit  = $detail['credit'] ?? 0;
                    $current_debit = $detail['debit'] ?? 0;
                    $debit  = ($detail['debit'] *  $exchangeValue)?? 0;
                    $credit = ($detail['credit'] *  $exchangeValue) ?? 0;

                    if (!empty($detail['id'])) {
                        $journalEntry->journalEntryDetails()
                            ->where('id', $detail['id'])
                            ->update([
                                'journal_id'     => $detail['journal_id'],
                                'name'           => $detail['name'] ?? null,
                                'customer_id'    => $detail['customer_id'] ?? null,
                                'vendor_id'      => $detail['vendor_id'] ?? null,
                                'cost_center_id' => $detail['cost_center_id'] ?? null,
                                'debit'          => $debit,
                                'credit'         => $credit,
                                'current_credit' => $current_credit,
                                'current_debit'  => $current_debit,
                                'description'    => $detail['description'] ?? null,
                                'modified_by'    => authActionSave()['by'],
                                'modified_by_type'=> authActionSave()['type'],
                            ]);
                    } else {
                        $journalEntry->journalEntryDetails()->create([
                            'journal_id'     => $detail['journal_id'],
                            'name'           => $detail['name'] ?? null,
                            'customer_id'    => $detail['customer_id'] ?? null,
                            'vendor_id'      => $detail['vendor_id'] ?? null,
                            'cost_center_id' => $detail['cost_center_id'] ?? null,
                            'debit'          => $debit,
                            'credit'         => $credit,
                            'current_credit' => $current_credit,
                            'current_debit'  => $current_debit,
                            'description'    => $detail['description'] ?? null,
                            'created_by'     => authActionSave()['by'],
                            'created_by_type'=> authActionSave()['type'],
                        ]);
                    }

                    $totalDebit  += $debit;
                    $totalCredit += $credit;
                    $current_total_debit += $current_debit;
                    $current_total_credit += $current_credit;
                }

                $journalEntry->update([
                    'total_debit'  => $totalDebit,
                    'total_credit' => $totalCredit,
                    'current_total_debit'  => $current_total_debit,
                    'current_total_credit' => $current_total_credit,
                ]);

                $journalEntry->refresh();
                if ($journalEntry->status === 'posted') {
                    $this->postJournalEntry($journalEntry);
                    $this->postCostCenter($journalEntry);
                }
                return ResponseWithSuccessData($lang, new JournalEntryResource($journalEntry), 1);
            });

        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function archive(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        // try {
            $journalEntry = JournalEntry::find($request->id);
            if (!$journalEntry) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            $is_active = $request->has('is_active') ? (int)$request->is_active : 0;
            $updateData = [
                'is_active'        => $is_active,
                'modified_by'      => authActionSave()['by'],
                'modified_by_type' => authActionSave()['type'],
            ];
            $this->updateJournalTree($journalEntry->id, $updateData);
            $journalEntry->refresh();
            return ResponseWithSuccessData($lang, new JournalEntryResource($journalEntry), 1);
        // } catch (\Exception $e) {
        //     return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        // }
    }

    private function updateJournalTree($journalEntryId, array $updateData)
    {
        $allIds = [$journalEntryId];
        $this->collectDescendantIds($journalEntryId, $allIds);
        JournalEntry::whereIn('id', $allIds)->update($updateData);
    }

    private function collectDescendantIds($parentId, array &$allIds)
    {
        $children = JournalEntry::where('parent_id', $parentId)->pluck('id');
        foreach ($children as $childId) {
            $allIds[] = $childId;
            $this->collectDescendantIds($childId, $allIds);
        }
    }

    public function delete(Request $request)
    {
        $lang =  $request->header('lang', 'en');
        try {
            $data = JournalEntry::find($request->id);
            if (!$data) {
                return respondError(($lang == 'en' ? 'Not existing any more' : 'غير موجود'), 404, $lang == 'en' ? 'This item is not existing any more' : 'هذا العنصر غير موجود');
            }

            if ($data->status == "posted") {
                return respondError(($lang == 'en' ? 'You can not deleted' : 'لا يمكن الحذف'), 404, $lang == 'en' ? 'You cannot delete this restriction because it has been posted.' : 'لا يمكنك حذف هذا القيد لانه تم ترحيله');
            }

            $data->journalEntryDetails()->delete();

            $data->deleted_by = authActionSave()['by'];
            $data->deleted_by_type = authActionSave()['type'];
            $data->save();
            $data->delete();

            return RespondWithSuccessRequest($lang, 1);
        } catch (\Exception $e) {
            return respondError(($lang == 'en' ? 'An error occurred' : 'حصل خطا'), 400, $lang == 'en' ? ['An error occurred during the addition process. Please try again.'] : ['حصل خطا اثناء عملية الاضافة من فضلك حاول مره اخرى']);
        }
    }

    public function postJournalEntry(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'draft') {
            return;
        }
        DB::transaction(function () use ($journalEntry) {
            foreach ($journalEntry->journalEntryDetails as $detail) {
                $journal = Journal::lockForUpdate()->find($detail->journal_id);
                if (!$journal) {
                    throw new \Exception("الحساب غير موجود: ID {$detail->journal_id}");
                }

                $debit  = $detail->debit ?? 0;
                $credit = $detail->credit ?? 0;
                $amount = $journal->account_type == "debit" ? ($debit - $credit) : ($credit - $debit);

                $journal->increment('debit', $debit);
                $journal->increment('credit', $credit);
                $journal->increment('balance', $amount);
                $journal->save();
                $this->updateParentBalances($journal->parent_id, $debit, $credit);
            }
        });
    }

    private function updateParentBalances($parentId, $debit, $credit)
    {
        if (!$parentId) return;
        $parent = Journal::lockForUpdate()->find($parentId);
        if ($parent) {
            $amount = $parent->account_type == "debit" ? ($debit - $credit) : ($credit - $debit);
            $parent->increment('debit', $debit);
            $parent->increment('credit', $credit);
            $parent->increment('balance', $amount);
            $parent->save();
            $this->updateParentBalances($parent->parent_id, $debit , $credit);
        }
    }

    public function postCostCenter(JournalEntry $journalEntry)
    {
        if ($journalEntry->status === 'draft') {
            return;
        }
        DB::transaction(function () use ($journalEntry) {
            foreach ($journalEntry->journalEntryDetails as $detail) {
                if($detail->cost_center_id){
                    $cost_center = CostCenter::lockForUpdate()->find($detail->cost_center_id);
                    if (!$cost_center) {
                        throw new \Exception("مركز التكلفة غير موجود: ID {$detail->cost_center_id}");
                    }

                    $debit  = $detail->debit ?? 0;
                    $credit = $detail->credit ?? 0;
                    $amount = $debit - $credit;

                    $cost_center->increment('debit', $debit);
                    $cost_center->increment('credit', $credit);
                    $cost_center->increment('balance', $amount);
                    $cost_center->save();
                    $this->updateCostCenterParentBalances($cost_center->parent_id, $debit, $credit);
                }
            }
        });
    }

    private function updateCostCenterParentBalances($parentId, $debit, $credit)
    {
        if (!$parentId) return;
        $parent = CostCenter::lockForUpdate()->find($parentId);
        if ($parent) {
            $amount = $debit - $credit;
            $parent->increment('debit', $debit);
            $parent->increment('credit', $credit);
            $parent->increment('balance', $amount);
            $parent->save();
            $this->updateCostCenterParentBalances($parent->parent_id, $debit , $credit);
        }
    }

    private function generateJournalEntryNumner($facilityId)
    {
        $lastEntry = JournalEntry::where('facility_id', $facilityId)
            ->lockForUpdate()
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastEntry || is_null($lastEntry->journal_entry_numner)) {
            $nextNumber = 1;
        } else {
            preg_match('/\d+$/', $lastEntry->journal_entry_numner, $matches);
            $lastNumber = $matches[0] ?? 0;
            $nextNumber = (int)$lastNumber + 1;
        }

        return 'JE-' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }
}
