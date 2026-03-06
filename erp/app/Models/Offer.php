<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Offer extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('offer');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name'];

    protected $fillable = [
        'branch_id',
        'name_ar',
        'name_en',
        'description_ar',
        'description_en',
        'image_ar',
        'image_en',
        'is_active',
        'start_date',
        'end_date',
        'discount_type',
        'discount_value',
        'created_by',
        'modified_by',
        'deleted_by',

    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    public function getNameAttribute()
    {

        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    public function details()
    {
        return $this->hasMany(OfferDetail::class, 'offer_id', 'id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function slider()
    {
        return $this->hasMany(Slider::class, 'offer_id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'offer_id',);
    }
}
