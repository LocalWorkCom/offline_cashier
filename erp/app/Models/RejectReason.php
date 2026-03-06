<?php
// app/Models/RejectReason.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class RejectReason extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('reason-list');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'supply_order_reject_reasons';
    protected $fillable = ['name_ar', 'name_en',  'created_by', 'updated_by', 'created_by_type', 'updated_by_type', 'deleted_by', 'deleted_by_type'];

    protected $appends = ['name'];
    public function supplyOrders()
    {
        return $this->hasMany(SupplyOrder::class);
    }
    public function getNameAttribute()
    {
        return Request()->header('lang') == 'en' ? $this->name_en : $this->name_ar;
    }

    public function purchaseRequests()
    {
        return $this->hasMany(PurchaseRequest::class, 'reject_reason_id');
    }
}
