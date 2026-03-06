<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Coupon extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('coupon');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['title', 'title_site'];
    protected $fillable = [
        'code',
        'type',
        'value',
        'minimum_spend',
        'apply_type',
        'usage_limit',
        'count_usage',
        'title_ar',
        'title_en',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by'
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];
    protected $casts = [
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function getTitleAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->title_en : $this->title_ar;
    }

     public function getTitleSiteAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->title_site_en : $this->title_site_ar;
    }
    public function getTitleAttribute2()
    {
        return app()->getLocale() === 'en' ? $this->title_en : $this->title_ar;
    }
    // Scope to get only active coupons
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('start_date')
                    ->orWhere('start_date', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    // Check if the coupon has been fully used
    public function isUsedUp()
    {
        return $this->usage_limit && $this->count_usage >= $this->usage_limit;
    }

    // Method to increment count usage
    public function incrementUsage()
    {
        $this->count_usage++;
        $this->save();
    }

    // Function to get coupon type label
    public function getTypeLabelAttribute()
    {
        return $this->type === 'percentage' ? 'Percentage' : 'Fixed Amount';
    }
    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_coupon')
            ->withPivot('dish_ids')
            ->withTimestamps();
    }

}
