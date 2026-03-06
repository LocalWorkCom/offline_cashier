<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class City extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('city');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    // Table name if it's not the plural form of the model
    protected $table = 'cities';
    protected $appends = ['name', 'name_site'];

    // Fillable fields
    protected $fillable = [
        'name_ar',
        'name_en',
        'country_id',
        'created_by',
        'updated_by',
    ];

    // Soft delete
    protected $dates = ['deleted_at'];

    public function getNameAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }
    public function getNameSiteAttribute()
    {
        return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    }

    /**
     * Get the country that owns the city.
     */
    public function country()
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    /**
     * Get the user who created the city.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the city.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the area that the city belongs to.
     */
    public function Area()
    {
        return $this->hasMany(Area::class);
    }
    public function areas()
    {
        return $this->belongsTo(Area::class);
    }
}
