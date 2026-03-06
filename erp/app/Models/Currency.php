<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Currency extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('currency');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    public function rates()
    {
        return $this->hasMany(CurrencyExchange::class);
    }

    public function latestRate()
    {
        return $this->hasOne(CurrencyExchange::class)->latest();
    }
    protected $table = 'currencies';

    protected $fillable = [
        'currency_ar',
        'currency_en',
        'currency_symbol',
        'currency_code',
        'decimal_number',
        'price_egp',
        'is_default',
        'is_active',
        'country_id',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type'
    ];

    protected $hidden = [
        // 'created_at',
        // 'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type'
    ];

    protected $appends = ['name'];

    public function getNameAttribute($value){
        return Request()->header('lang') == "en" ? $this->currency_en : $this->currency_ar;
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id', 'id');
    }

    // public function currency_exchang()
    // {
    //     return $this->belongsTo(CurrencyExchange::class, 'country_id', 'id');
    // }

    public function currencyExchange()
    {
        return $this->hasMany(CurrencyExchange::class, 'currency_id', 'id');
    }

    public function journalEntry()
    {
        return $this->hasMany(JournalEntry::class, 'currency_id', 'id');
    }

    public function facility()
    {
        return $this->hasMany(Facility::class, 'currency_id', 'id');
    }

    public function createdBy()
    {
        if ($this->created_by_type === 'employee') {
            return $this->belongsTo(Employee::class, 'created_by');
        }

        if ($this->created_by_type === 'user') {
            return $this->belongsTo(User::class, 'created_by');
        }

        return $this->belongsTo(Employee::class, 'created_by')->withDefault();
    }

    public function modifiedBy()
    {
        if ($this->modified_by_type === 'employee') {
            return $this->belongsTo(Employee::class, 'modified_by');
        }

        if ($this->modified_by_type === 'user') {
            return $this->belongsTo(User::class, 'modified_by');
        }

        return $this->belongsTo(Employee::class, 'modified_by')->withDefault();
    }

    public function deletedBy()
    {
        if ($this->deleted_by_type === 'employee') {
            return $this->belongsTo(Employee::class, 'deleted_by');
        }

        if ($this->deleted_by_type === 'user') {
            return $this->belongsTo(User::class, 'deleted_by');
        }

        return $this->belongsTo(Employee::class, 'deleted_by')->withDefault();
    }
}
