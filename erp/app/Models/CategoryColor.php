<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class CategoryColor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('category-color');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'category_colors'; // Optional if the table name follows Laravel's convention

    protected $fillable = [
        'color_id',
        'created_by',
        'category_id',
    ];
    
    protected $hidden = [
        'created_by',
        'modify_by',
        'deleted_at',
        'deleted_by',
        'created_at',
        'updated_at',
    ];
    /**
     * Get the color associated with the category color.
     */
    public function color()
    {
        return $this->belongsTo(Color::class);
    }

    /**
     * Get the category associated with the category color.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
