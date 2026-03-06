<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasingBudget extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'purchasing_budgets';
    protected $fillable = [
        'month',
        'year',
        'base_amount',
        'increase_amount',
        'increase_count',
        'remaining_amount',
        'notes',
        'created_by',
        'modified_by',
        'deleted_by',
        'is_active',
    ];


    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

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
    public function logs()
    {
        return $this->hasMany(PurchasingBudgetLog::class, 'purchasing_budget_id');
    }
}
