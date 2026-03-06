<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;

class ApplicationExport implements FromArray
{
    protected $answers;

    public function __construct(array $answers)
    {
        $this->answers = $answers;
    }

    public function array(): array
    {
        $formatted = [['Question', 'Answer']];

        foreach ($this->answers as $answer) {
            $question = $answer['question'] ?? 'Unknown Question';

            // Handle missing or empty values safely
            $value = $answer['value'] ?? 'No Answer';

            if (is_array($value)) {
                $value = implode(', ', array_filter($value));
            }

            $formatted[] = [$question, $value];
        }

        return $formatted;
    }
}
