<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PricingDealShipment  extends Model
{
    use HasFactory, LogsActivity;

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
        'pricing_deal_id',
        'vendor_id',
        'from_address',
        'type_shipment',
        'to_address',
        'price',
        'pricing_basis',
        'notes',
        'file',
    ];

    /*************************
     *      RELATIONS
     *************************/

    // Parent Deal
    public function pricingDeal()
    {
        return $this->belongsTo(PricingDeal::class);
    }

    // Vendor (source of shipping)
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
