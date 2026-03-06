<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
class QuestionsImport implements ToArray, WithHeadingRow
{
    /**
     * @param array $array
     * @return array
     */
    public function array(array $array)
    {
        return $array;
    }

    /**
     * @return int
     */
    public function headingRow(): int
    {
        return 1; // This assumes your Excel has headers in the first row
    }

}
