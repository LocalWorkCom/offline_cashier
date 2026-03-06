<?php 

namespace App\Models;

use App\Models\CuisineCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class ChefCuisineCategory extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('chef-cuisine-category');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'chef_cuisine_categories';

    protected $fillable = [
        'employee_id',
        'cuisine_category_id',
        'dishes', // Add this
        'created_by',
        'updated_by',
    ];

    protected $dates = ['deleted_at'];
    
    // Cast the dishes field to array
    protected $casts = [
        'dishes' => 'array',
    ];

    public function chef()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function cuisineCategory()
    {
        return $this->belongsTo(CuisineCategory::class, 'cuisine_category_id');
    }
}