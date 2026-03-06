<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class ReasonPurchaseRequest extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('reason-purchase-request');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $fillable = [
        'name_ar',
        'name_en',
        'is_active',
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
    ];
    protected $dates = ['deleted_at'];

    protected $hidden = [
        'created_by',
        'created_by_type',
        'modified_by',
        'modified_by_type',
        'deleted_by',
        'deleted_by_type',
        // 'deleted_at',
        // 'created_at',
        // 'updated_at',
    ];

    protected $casts = [
        'is_active' => 'integer',
    ];
    protected $appends = ['name'];
    public function getNameAttribute()
    {
        return request()->header('lang') == 'en' ? $this->name_en : $this->name_ar;
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class,'reason_pr_id');
    }
}
