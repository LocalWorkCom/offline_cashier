<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class Dish extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('dish');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['name', 'description', 'name_site', 'description_site', 'img'];

    protected $fillable = [
        'name_en',
        'name_ar',
        'item_code_id',
        'description_en',
        'description_ar',
        'category_id',
        'cuisine_id',
        'price',
        'image',
        'code',
        'time',
        'time_cancelation',
        'is_active',
        'has_sizes',
        'has_addon',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_type',
        'deleted_type',
        'modified_type',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at',
        'img',
    ];

    public function getImgAttribute()
    {
        return $this->image;
    }

    public function getNameAttribute()
    {

        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getDescriptionAttribute()
    {

        return request()->header('lang', 'ar') === 'en' ? $this->description_en : $this->description_ar;
    }

    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    public function getDescriptionSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->description_en : $this->description_ar;
    }

    // Relationships
    public function dishCategory()
    {
        return $this->belongsTo(DishCategory::class, 'category_id');
    }

    // Cuisine relationship
    public function cuisine()
    {
        return $this->belongsTo(Cuisine::class, 'cuisine_id');
    }

    public function recipes()
    {
        return $this->hasMany(DishDetail::class, 'dish_id');
    }

    public function branches()
    {
        return $this->belongsToMany(Branch::class, 'branch_dish');
    }

    public function addons()
    {
        return $this->belongsToMany(Dish::class, 'dish_addons', 'dish_id', 'addon_id');
    }

    public function dishSizes()
    {
        return $this->hasMany(DishSize::class, 'dish_id');
    }
    public function sizes()
    {
        return $this->hasMany(DishSize::class);
    }
    public function details()
    {
        return $this->hasMany(DishDetail::class, 'dish_id');
    }
    public function dishAddonsDetails()
    {
        return $this->hasMany(DishAddon::class, 'dish_id');
    }

    public function sizeDefaults()
    {
        return $this->hasOne(DishSize::class)->where('default_size', 1);
    }
    public function branchMenuSizes()
    {
        return $this->hasMany(BranchMenuSize::class);
    }

    public function getMenuSizeDefaultAttribute()
    {
        // return $this->branchMenuSizes()
        //     ->whereHas('dishSizes', function ($query) {
        //         $query->where('default_size', 1);
        //     })
        //     ->with('dishSizes')
        //     ->orderBy('updated_at', 'desc')
        //     ->first();

        return $this->branchMenuSizes()->whereHas('dishSizes', function ($q){
                $q->where('default_size', 1);
            })
            ->first();
    }

    public function branchMenus()
    {
        return $this->hasMany(BranchMenu::class, 'dish_id', 'id');
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'dish_id'); // Make sure 'dish_id' is the correct foreign key
    }
    public function sliders()
    {
        return $this->hasMany(Slider::class, 'dish_id', 'id'); // One-to-many relationship
    }
    public function discounts()
    {
        return $this->belongsToMany(Discount::class, 'dish_discount', 'dish_id', 'discount_id');
    }

    public function offerDetails()
    {
        return $this->hasMany(OfferDetail::class, 'type_id', 'id')
            ->whereNull('deleted_at')
            ->whereHas('offer', function ($query) {
                $query->whereNull('deleted_at'); // Check if the associated offer is not deleted
            });
    }
    public function itemCode()
    {
        return $this->belongsTo(ItemCode::class);
    }
    public function getItemCodeNameAttribute()
    {
        $lang = app()->getLocale();
        return $lang === 'ar' ? optional($this->itemCode)->codeNameAr : optional($this->itemCode)->codeName;
    }
}
