<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class InvoiceDetails extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('invoice-details');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    public function invoices()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function OrderDetails()
    {
        if($this->type == "dish"){
            return $this->belongsTo(OrderDetail::class, 'details_id');
        }else{
            return $this->belongsTo(OrderAddon::class, 'details_id');
        }
    }


    public function orderDetail()
    {
        return $this->belongsTo(OrderDetail::class, 'details_id');
    }

    public function orderAddon()
    {
        return $this->belongsTo(OrderAddon::class, 'details_id');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

}
