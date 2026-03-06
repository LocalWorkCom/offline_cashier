<?php

return [
 'custom' => [
        'name_ar' => [
            'required' => 'The Arabic name field is required.',
            'string'   => 'The Arabic name must be a string.',
            'max'      => 'The Arabic name may not be greater than 255 characters.',
            'unique'   => 'The Arabic name has already been taken.',
        ],
        'name_en' => [
            'required' => 'The English name field is required.',
            'string'   => 'The English name must be a string.',
            'max'      => 'The English name may not be greater than 255 characters.',
            'unique'   => 'The English name has already been taken.',
        ],
    ],
   
];
