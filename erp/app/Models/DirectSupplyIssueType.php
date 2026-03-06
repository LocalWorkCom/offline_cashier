<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class DirectSupplyIssueType extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('issue-type');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'direct_supply_issue_types';
    protected $fillable = [
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'status',
    ];
    protected $appends = [
        'name',
        'description',
    ];
    public function getNameAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->title_en : $this->title_ar;
    }
    public function getDescriptionAttribute($value)
    {
        return Request()->header('lang') == "en" ? $this->description_en : $this->description_ar;
    }
    public function issues()
    {
        return $this->hasMany(DspItemIssue::class, 'issue_type_id');
    }
}
