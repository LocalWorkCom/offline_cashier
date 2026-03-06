<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class OrderTransaction extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('order-transaction');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    // Table associated with the model
    protected $table = 'order_transactions';

    // The attributes that are mass assignable
    protected $fillable = [
        'payment_status',
        'payment_method',
        'transaction_id',
        'paid',
        'date',
        'refund',
        'order_id',
        'invoice_id',
        'coupon_id',
        'discount_id',
        'reference_number'
    ];

    protected $hidden = [
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'modify_by',
        'deleted_at',


    ];

    // The attributes that should be cast to native types
    // protected $casts = [
    //     'is_valid' => 'boolean',
    // ];

    // Define relationships
   
    public function Order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
    
}
