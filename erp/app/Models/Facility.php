<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Facility extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name_ar',
        'is_active',
        'currency_id',
        'country_id',
        'is_active',
        'code',
        'logo',
        'email',
        'address',
        'tax_id_number',
        'commercial_registration',
        'commercial_registration_number',
        'language',
        'vat_registration_number',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $casts = [
        // 'is_active' => 'boolean',
    ];

    protected $appends = ['name'];

    public function getNameAttribute($value){
        return Request()->header('lang') == "en" ? $this->name_ar : $this->name_ar;
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(Employee::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }

    public function logs()
    {
        return $this->hasMany(FacilityLog::class);
    }

    public function costCenter()
    {
        return $this->hasMany(CostCenter::class);
    }

    public function journalEntry()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
