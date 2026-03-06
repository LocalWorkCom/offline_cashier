<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class OfferDetail extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('offer-detail');
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
        'offer_id',
        'offer_type',
        'type_id',
        'count',
        'discount',
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

    public function offer()
    {
        return $this->belongsTo(Offer::class,'offer_id','id')->where('deleted_at',null);
    }

    public function getTypeName($lang)
    {
        switch ($this->offer_type) {
            case 'dishes':
                return optional(Dish::find($this->type_id))->{"name_{$lang}"} ?? null;

            case 'addons':
                return optional(Recipe::where('type', 2)->find($this->type_id))->{"name_{$lang}"} ?? null;

            case 'products':
                return optional(Product::find($this->type_id))->{"name_{$lang}"} ?? null;

            default:
                return null;
        }
    }

    public function getOfferTypeName($lang)
    {
        return match ($this->offer_type) {
            'dishes' => $lang === 'en' ? 'dishes' : 'الأطباق',
            'addons' => $lang === 'en' ? 'addons' : 'الإضافات',
            'products' => $lang === 'en' ? 'products' : 'المنتجات',
            default => null,
        };
    }

    public function dish()
    {
        return $this->belongsTo(Dish::class,'type_id','id')->where('deleted_at',null);
    }
    public function addon()
    {
        return $this->belongsTo(Recipe::class,'type_id','id')->where('type',2)->where('deleted_at',null);
    }public function product()
    {
        return $this->belongsTo(Product::class,'type_id','id')->where('type','complete')->where('deleted_at',null);
    }

}
