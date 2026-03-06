<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalEntryDepartment extends Model
{
    use HasFactory;

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'ar' ? $this->name_ar : $this->name_en;
    }

}
