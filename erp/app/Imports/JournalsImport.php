<?php

namespace App\Imports;

use App\Models\Journal;
use App\Models\Currency;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Illuminate\Support\Facades\Log;
use Throwable;

class JournalsImport implements ToModel,WithHeadingRow,WithValidation,SkipsOnFailure,WithChunkReading
{
    use Importable, SkipsFailures;

    protected $facility_id;
    protected $createdBy;
    protected $createdByType;
    protected $codeToId = [];
    protected $currencyCache = [];
    protected $pendingParents = [];

    public function __construct($facilityId, $createdBy, $createdByType)
    {
        // $employee = auth('employee')->user();
        // $this->facility_id = $employee->employeeFacility->facility_id;
        $this->facility_id = $facilityId;
        $this->createdBy = $createdBy;
        $this->createdByType = $createdByType;
        $this->currencyCache = $this->getFacilityCurrencyForImport();
    }

    private function normalize($value)
    {
        if (!$value) return null;

        $value = trim(preg_replace('/\s+/', ' ', $value));
        return mb_strtolower($value);
    }

    private function getFacilityCurrencyForImport()
    {
        $currencies = Currency::select('id', 'currency_ar', 'currency_en', 'currency_symbol', 'currency_code')
            ->with(['currencyExchange' => fn($q) => $q->where('facility_id', $this->facility_id)])
            ->get();

        $cache = [];

        foreach ($currencies as $currency) {
            $id = $currency->id;

            $values = [
                $currency->currency_ar,
                $currency->currency_en,
                $currency->currency_symbol,
                $currency->currency_code,
            ];

            foreach ($values as $value) {
                if (!$value) continue;

                $clean = $this->normalize($value);
                if ($clean) {
                    $cache[$clean] = $id;
                }
            }
        }

        return $cache;
    }

    public function model(array $row)
    {
        $code = trim($row['code'] ?? '');
        if ($code === '') return null;

        $parentCode = trim($row['parent_code'] ?? '') ?: null;
        $currencyId = null;
        if (!empty($row['currency_id'])) {
            $key = $this->normalize($row['currency_id']);
            $currencyId = $this->currencyCache[$key] ?? null;

            if (!$currencyId) {
                Log::warning(" العملة غير موجودة: '{$row['currency_id']}' → الحساب: {$code}");
            }
        }

        $accountType = strtolower(trim($row['account_type'] ?? ''));
        $type        = strtolower(trim($row['type'] ?? ''));

        $parentId = null;
        $level = 1;

        if ($parentCode) {
            $parentId = $this->codeToId[$parentCode] ?? Journal::where('code', $parentCode)
                                                               ->where('facility_id', $this->facility_id)
                                                               ->value('id');

            if ($parentId) {
                $parentLevel = Journal::where('id', $parentId)->value('level') ?? 1;
                $level = $parentLevel + 1;
            } else {
                $this->pendingParents[] = [
                    'child_code'  => $code,
                    'parent_code' => $parentCode,
                ];
            }
        }

        $journal = Journal::with(['children', 'journalEntryDetails'])
            ->where('code', $code)
            ->where('facility_id', $this->facility_id)
            ->first();

        if ($journal) {
            $updatable = [
                'name_ar' => trim($row['name_ar'] ?? $journal->name_ar),
                'name_en' => trim($row['name_en'] ?? $journal->name_en ?? $journal->name_ar),
            ];

            if ($journal->children->isEmpty() && $journal->journalEntryDetails->isEmpty()) {
                $updatable += [
                    'type'         => $type ?: $journal->type,
                    'account_type' => $accountType ?: $journal->account_type,
                    'balance_type' => $accountType ?: $journal->balance_type,
                    'currency_id'  => $currencyId ?? $journal->currency_id,
                    'parent_id'    => $parentId ?? $journal->parent_id,
                    'level'        => $level,
                ];
            }

            $journal->update($updatable);
            $account = $journal;
        } else {
            $account = Journal::create([
                'code'            => $code,
                'facility_id'     => $this->facility_id,
                'name_ar'         => trim($row['name_ar'] ?? ''),
                'name_en'         => trim($row['name_en'] ?? ($row['name_ar'] ?? '')),
                'type'            => $type,
                'account_type'    => $accountType,
                'balance_type'    => $accountType,
                'currency_id'     => $currencyId,
                'parent_id'       => $parentId,
                'level'           => $level,
                'is_active'       => 1,
                'created_by'      => $this->createdBy,
                'created_by_type' => $this->createdByType,
            ]);
        }

        $this->codeToId[$code] = $account->id;
        return $account;
    }

    public function rules(): array
    {
        return [
            'code'         => ['required','regex:/^[a-zA-Z0-9]+$/'],
            'name_ar'      => 'required|string',
            'type'         => 'nullable|in:assets,liabilities,equity,revenue,expense',
            'account_type' => 'required|in:debit,credit',
            'parent_code'  => 'nullable|numeric',
            'currency_id'  => 'nullable|string',
        ];
    }

    public function onError(Throwable $e)
    {
        Log::error('خطأ استيراد حساب: ' . $e->getMessage());
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function fixParentsAfterImport()
    {
        if (empty($this->pendingParents)) return;

        $codeMap = Journal::where('facility_id', $this->facility_id)
            ->pluck('id', 'code')
            ->toArray();

        foreach ($this->pendingParents as $item) {
            $childCode  = $item['child_code'];
            $parentCode = $item['parent_code'];

            if (isset($codeMap[$parentCode]) && isset($codeMap[$childCode])) {
                $parentId    = $codeMap[$parentCode];
                $childId     = $codeMap[$childCode];
                $parentLevel = Journal::where('id', $parentId)->value('level') ?? 1;

                Journal::where('id', $childId)->update([
                    'parent_id' => $parentId,
                    'level'     => $parentLevel + 1,
                ]);
            } else {
                Log::warning(" الأب أو الطفل غير موجود child_code: {$childCode}, parent_code: {$parentCode}");
            }
        }
    }

    // public function failures()
    // {
    //     foreach ($this->failures() as $failure) {
    //         Log::warning('Excel validation error', [
    //             'row'       => $failure->row(),
    //             'attribute' => $failure->attribute(),
    //             'errors'    => $failure->errors(),
    //             'values'    => $failure->values(),
    //         ]);
    //     }
    // }
}
