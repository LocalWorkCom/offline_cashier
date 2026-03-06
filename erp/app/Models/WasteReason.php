<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class WasteReason extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('waste-reason');
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

    protected $fillable = ['name_ar', 'name_en'];
    public function getNameAttribute()
    {
        $lang = app()->getLocale();
        return $lang === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function wasteReportItems()
    {
        return $this->hasMany(WasteReportItem::class, 'waste_reason_id');
    }
}
