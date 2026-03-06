<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait UUID
{
    protected static function boot ()
    {
        // Boot other traits on the Model
        parent::boot();
        static::creating(function ($model) {
            $model->id = (string)Str::uuid();
        });
    }

    // Tells the database not to auto-increment this field
    public function getIncrementing ()
    {
        return false;
    }

    // Helps the application specify the field type in the database
    public function getKeyType ()
    {
        return 'string';
    }
}