<?php
[
    'duplicate_entry' => 'Bonus settings with these values already exist.',

    'custom' => [
        'department_id' => [
            'required' => 'The department is required.',
            'exists' => 'The selected department does not exist.',
        ],
        'employee_id' => [
            'required' => 'The employee is required.',
            'exists' => 'The selected employee does not exist.',
        ],
        'bonus_type' => [
            'required' => 'The bonus type is required.',
            'in' => 'The selected bonus type is invalid.',
        ],
        'bonus_value' => [
            'numeric' => 'The bonus value must be a number.',
            'min' => 'The bonus value must be at least :min.',
            'max' => 'The bonus value may not be greater than :max.',
        ],
        'reason' => [
            'string' => 'The reason must be a valid string.',
        ],
        'payout_date' => [
            'required' => 'The payout date is required.',
            'date' => 'The payout date must be a valid date.',
        ],
    ],
];
