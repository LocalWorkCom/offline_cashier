<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class ProductBrandCategoryColor extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('product-brand-category-color');
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
        'product_brand_id',
        'category_color_id',
    ];

    // Relationships
    public function productBrand(): BelongsTo
    {
        return $this->belongsTo(ProductBrand::class);
    }

    public function categoryColor(): BelongsTo
    {
        return $this->belongsTo(CategoryColor::class);
    }
}
