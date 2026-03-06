<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Insurance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'insurances';

    protected $fillable = [
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'subscription_num',
        'is_active',
        'automatic_transfer',
        'company_percentage',
        'employee_percentage',
        'journal_ids',
        'created_by',
        'modified_by',
        'deleted_by',
        'subscription_expenses',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'journal_ids' => 'array',
        'is_active' => 'boolean',
        'automatic_transfer' => 'boolean',
        'company_percentage' => 'double',
        'employee_percentage' => 'double',
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getDescriptionAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->description_en : $this->description_ar;
    }

    // public function createdByUser()
    // {
    //     return $this->belongsTo(Employee::class, 'created_by', 'id');
    // }

    // public function modifiedByUser()
    // {
    //     return $this->belongsTo(Employee::class, 'modified_by', 'id');
    // }

    // public function deletedByUser()
    // {
    //     return $this->belongsTo(Employee::class, 'deleted_by', 'id');
    // }

    public function createdByUser()
    {
        return $this->belongsTo(Employee::class, 'created_by', 'id');
    }

    public function modifiedByUser()
    {
        return $this->belongsTo(Employee::class, 'modified_by', 'id');
    }

    public function deletedByUser()
    {
        return $this->belongsTo(Employee::class, 'deleted_by', 'id');
    }
}
