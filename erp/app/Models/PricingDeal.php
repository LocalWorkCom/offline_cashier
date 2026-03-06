<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PricingDeal extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('price-deal');
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
        'vendor_id',
        'deal_type',
        'file',
        'start_date',
        'end_date',
        'period',
        'total_before_negotiated',
        'total_after_negotiated',
        'delivery_date',
        'payment_terms',
        'penalty_clause',
        'discount_by_quantity',
        'terms_conditions',
        'notes',
        'status',
        'is_active',
    ];

    /*************************
     *      RELATIONS
     *************************/

    // Vendor
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    // Items
    public function items()
    {
        return $this->hasMany(PricingDealItem::class);
    }

    // Shipment Information (one deal → one shipment record)
    public function shipment()
    {
        return $this->hasOne(PricingDealShipment::class);
    }
    public function purchaseOrderDeals()
    {
        return $this->hasMany(PurchaseOrderDeal::class, 'price_deal_id');
    }
}
