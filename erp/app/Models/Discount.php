<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Discount extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('discount');
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
        'name_en',
        'name_ar',
        'type',
        'value',
        'start_date',
        'end_date',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by'
    ];

    protected $hidden = [
        'name_en',
        'name_ar',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

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

    public function getTypeLabelAttribute()
    {
        return $this->type === 'percentage' ? 'Percentage' : 'Fixed Amount';
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_discount', 'discount_id', 'branch_id');
    }

    // public function dishes()
    // {
    //     return $this->belongsToMany(Dish::class, 'dish_discount');
    // }
    public function dishDiscounts()
    {
        return $this->hasMany(DishDiscount::class);
    }
    public function discount_dishes()
    {
        return $this->belongsToMany(Dish::class, 'dish_discount')
            ->withPivot('dish_id') 
            ->wherePivotNull('deleted_at') 
            ->withTimestamps(); 
    }       

    public function dishes()
    {
        return $this->belongsToMany(Dish::class, 'dish_discount', 'discount_id', 'dish_id')
            ->whereNull('dish_discount.deleted_at'); // Ensure only non-deleted dishes
    }
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
    public function sliders()
    {
        return $this->hasMany(Slider::class, 'discount_id', 'id');
    }

    // Add deleting event
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($discount) {
            foreach ($discount->dishDiscounts as $dishDiscount) {
                // Check if sliders exist for this dishDiscount
                if ($dishDiscount->sliders()->exists()) {
                    // Delete related sliders (force delete if they are soft deleted)
                    $dishDiscount->sliders()->delete();

                    // Optionally, you could also check if you want to delete the entire dishDiscount
                    // $dishDiscount->delete(); // If you want to delete the association record too
                }
            }
        });
    }
}
