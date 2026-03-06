<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class ReturnInvoiceRequest extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('return-invoice-request');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'return_invoice_requests';
    protected $appends = ['name'];
    protected $fillable = [
        'invoice_id',
        'invoice_details_ids',
        'reason',
        'reject_resone',
        'request_type',
        'date',
        'time',
        'status',
        'request_num',
        'is_active',
        'approved_by',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    protected $casts = [
        'invoice_details_ids' => 'array',
        'date' => 'date',
        'time' => 'datetime:H:i',
        'is_active' => 'boolean',
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }  
     public function admin()
    {
        return $this->belongsTo(User::class, 'created_by');
    }  
}
