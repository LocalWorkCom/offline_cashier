<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CurrencyExchange extends Model
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
    protected $table = 'currency_exchanges';

    protected $fillable = [
        'currency_id',
        'exchange_currency_id',
        'facility_id',
        'exchange_value',
        'exchange_value_before',
        'date',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type'
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function exchangeCurrency()
    {
        return $this->belongsTo(Currency::class, 'exchange_currency_id');
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
