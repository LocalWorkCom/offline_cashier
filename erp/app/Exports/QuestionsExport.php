<?php

namespace App\Exports;

use App\Models\JobApplication;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class QuestionsExport implements FromCollection, WithHeadings
{
    protected $questions;

    public function __construct(array $questions)
    {
        $this->questions = $questions;
    }

    public function collection()
    {
        return collect($this->questions);
    }

    public function headings(): array
    {
        return ['Question', 'Type', 'Options', 'IsRequired'];
    }
}
