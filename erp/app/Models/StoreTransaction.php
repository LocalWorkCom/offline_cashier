<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class StoreTransaction extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('store-transaction');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $hidden = [
        'creator_by',
        'to_id',
        'created_by',
        'deleted_by',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'purchase_order_items_id',
        'product_stores_id',
        'sku',
        'production_date',
        'expired_date',
        'validity_period_days',
        'user_id',
        'store_id',
        'type',
        'to_type',
        'to_id',
        'date',
        'total',
        'total_price',
        'invoice_num',
        'invoice_id',
        'branch_id',
    ];

    protected $casts = [
        'production_date' => 'date',
        'expired_date' => 'date',
    ];

    // Relationships

    public function creatorBy()
    {
        return $this->belongsTo(User::class, 'creator_by');
    }

    public function toId()
    {
        return $this->belongsTo(Store::class, 'to_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // public function allStoreTransactionDetails()
    // {
    //     return $this->hasMany(StoreTransactionDetails::class, 'store_transaction_id', 'id');
    // }

    public function purchaseInvoice()
    {
        return $this->belongsTo(PurchaseInvoice::class, 'purchase_invoice_id');
    }

    public function stores()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_items_id');
    }

    public function productStore()
    {
        return $this->belongsTo(ProductStore::class, 'product_stores_id');
    }
}
