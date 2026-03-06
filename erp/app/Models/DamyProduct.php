<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class DamyProduct extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('damy-product');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'damy_products';

    protected $fillable = [
        'name',
        'category',
        'brand',
        'unit',
        'quantity',
        'note',
        'status',
        'pr_id',
        'added_live_by',
    ];

    /**
     * Relationship with PurchaseRequest
     */
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'pr_id');
    }

    /**
     * Relationship with Employee (who added the live product)
     */
    public function addedBy()
    {
        return $this->belongsTo(Employee::class, 'added_live_by');
    }
}
