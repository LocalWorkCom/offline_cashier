<?php

namespace App\Exports;

use App\Models\Journal;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class JournalsExport implements FromCollection, WithHeadings
{
    protected $facility_id;

    public function __construct($facilityId)
    {
        $this->facility_id = $facilityId;
    }

    public function collection()
    {
        $journals = Journal::where('facility_id', $this->facility_id)->where('level', 1)
            ->with('currency')
            ->get();

        return $journals->map(function($journal) {
            return [
                'code'         => $journal->code,
                'name_ar'      => $journal->name_ar,
                'name_en'      => $journal->name_en,
                'type'         => $journal->type,
                'parent_code'  => optional($journal->parent)->code ?? null,
                'account_type' => $journal->account_type,
                'currency'     => $journal->currency->currency_ar ?? null,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'code',
            'name_ar',
            'name_en',
            'type',
            'parent_code',
            'account_type',
            'currency_id',
        ];
    }
}
