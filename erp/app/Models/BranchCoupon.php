<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchCoupon extends Model
{
    use HasFactory, LogsActivity;
    protected $table = 'branch_coupon';

    // protected $casts = [
    //     'dish_ids' => 'array',
    // ];

    // Add this if you need timestamps
    public $timestamps = true;

    // Add fillable if needed
    protected $fillable = [
        'coupon_id',
        'branch_id',
        'dish_ids',
    ];
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-coupon');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    public function orders()
    {
        return $this->hasMany(Order::class, 'coupon_id', 'coupon_id');
    }
    public function dishes()
    {
        return $this->whereNotNull('dish_ids')
            ->get()
            ->map(function ($item) {
                $ids = json_decode($item->dish_ids, true);
                return Dish::whereIn('id', $ids)->get();
            });
    }
}
