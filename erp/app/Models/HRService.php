<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HRService extends Model
{
    use HasFactory;
    protected  $table = 'hr_services';
    protected $appends = ['name'];
    protected $fillable = [
        'key',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'created_by',
        'created_by_type',
        'updated_by',
        'updated_by_type',
        'deleted_by',
        'deleted_by_type'
    ];
    protected $hidden = ['key'];
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }
}
