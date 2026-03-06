<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class CategorySize extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('category-size');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'category_sizes'; // Optional if the table name follows Laravel's convention

    protected $fillable = [
        'size_id',
        'category_id',
        'created_by',

    ];
    
    protected $hidden = [
        'created_by',
        'deleted_by',
        'modify_by',
        'created_at',
        'updated_at',
        'deleted_at',

    ];
    /**
     * Get the size associated with the Category size.
     */
    public function size()
    {
        return $this->belongsTo(Size::class, 'size_id');
    }

    /**
     * Get the Category associated with the Category size.
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
