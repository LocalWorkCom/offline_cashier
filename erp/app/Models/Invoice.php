<?php

namespace App\Models;

use App\Models\Tip;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
// use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
// use Illuminate\Database\Eloquent\SoftDeletes;
// use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('invoice');
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
        'order_id',
        'order_type',
        'invoice_type',
        'invoice_num',
        'date',
        'time',
        'total_before_tax',
        'total_after_tax',
        'tax',
        'service_fees',
        'coupon_value',
        'coupon_id', // استخدام coupon_id للعلاقة بدلاً من الحقول المباشرة
        'make_type',
        'parent_id',
        'status',
        'payment_status',
        'payment_method',
        'payment_date',
        'payment_amount',
        'payment_currency',
        'payment_currency_rate',
        'payment_currency_symbol',
        'payment_currency_code',
        'payment_currency_name',
        'is_active',
        'created_by'
    ];


    public function orders()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function invoiceDetails()
    {
        return $this->hasMany(InvoiceDetails::class, 'invoice_id');
    }

    public function returnInvoiceRequests()
    {
        return $this->hasMany(ReturnInvoiceRequest::class, 'invoice_id');
    }

    public function parentInvoice()
    {
        return $this->belongsTo(Invoice::class, 'parent_id');
    }

    public function childInvoices()
    {
        return $this->hasMany(Invoice::class, 'parent_id');
    }

    public function orderTransactions()
    {
        return $this->hasMany(OrderTransaction::class, 'invoice_id');
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function tips()
    {
        return $this->hasMany(Tip::class, 'invoice_id');
    }
}

