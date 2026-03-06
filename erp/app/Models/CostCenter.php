<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CostCenter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cost_centers';

    protected $fillable = [
        'name_ar',
        'name_en',
        // 'description_ar',
        // 'description_en',
        'is_active',
        'code',
        'debit',
        'credit',
        'balance',
        'facility_id',
        'company_id',
        'branch_id',
        'parent_id',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $appends = ['name'];

    public function getNameAttribute()
    {
        return $this->name_ar;
    }

    public function getTypeAttribute()
    {
        if($this->branch_id == null && $this->company_id == null){
            return 1; //independent
        }elseif($this->branch_id == null && $this->company_id != null){
            return 2; //company
        }elseif($this->branch_id != null && $this->company_id == null){
            return 3; //branch
        }
    }

    public function getDescriptionAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->description_en : $this->description_ar;
    }

    // public function getTotalCreditAttribute()
    // {
    //     return $this->journalEntryDetails->sum();
    // }

    // public function getTotalDebitAttribute()
    // {
    //     return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    // }

    // public function getTotalBalanceAttribute()
    // {
    //     return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    // }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyProfileSetting::class, 'company_id', 'id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id', 'id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class, 'parent_id', 'id');
    }

    public function child()
    {
        return $this->hasMany(CostCenter::class, 'parent_id', 'id');
    }

    public function childrenRecursive()
    {
        return $this->child()->with('childrenRecursive');
    }

    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetails::class, 'cost_center_id', 'id');
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


}
