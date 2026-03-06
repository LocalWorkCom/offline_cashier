<?php

return [
    'document_type_id' => [
        'required' => 'The document type field is required.',
        'exists' => 'The selected document type does not exist.',
    ],
    'employee_id' => [
        'required' => 'The employee field is required.',
        'exists' => 'The selected employee does not exist.',
    ],
    'file' => [
        'required' => 'You must upload a document file.',
        'file' => 'The file must be a valid file.',
        'mimes' => 'Allowed file types are: pdf, jpg, jpeg, png, doc, docx.',
        'max' => 'The file must not exceed 2MB.',
    ]
];
