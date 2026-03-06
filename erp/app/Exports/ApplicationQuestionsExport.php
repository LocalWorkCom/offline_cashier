<?php

namespace App\Exports;

use App\Models\JobApplication;
use Maatwebsite\Excel\Concerns\FromCollection;

class ApplicationQuestionsExport implements FromCollection
{
    protected $questions;

    public function __construct($questions)
    {
        $this->questions = $questions;
    }

    public function collection()
    {
        // Map the questions array into an Excel-compatible format
        return collect($this->questions)->map(function ($question) {
            return [
                'question' => $question['question'],  // Question text
                'type' => $question['type'],         // Question type (radio, text, checkbox, etc.)
            ];
        });
    }
}
