<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PurchaseRequestItem extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('purchase-order');
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
        'purchase_request_id',
        'product_brand_id',
        'ordered_quantity',
        'unit_id',
        'note',
        'current_stock_level',
    ];
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $casts = [
        'ordered_quantity' => 'decimal:2',
        'current_stock_level' => 'decimal:2',
    ];
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class);
    }
    public function product()
    {
        return $this->belongsTo(ProductBrand::class ,'product_brand_id')->withDefault();
    }
    public function unit()
    {
        return $this->belongsTo(Unit::class ,'unit_id')->withDefault();
    }
}
