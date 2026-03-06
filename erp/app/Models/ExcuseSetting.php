<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class ExcuseSetting extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('excuse-setting');
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
        'max_daily_hours',
        'max_monthly_hours',
        'before_request_period',
        'is_paid',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
    ];

    /**
     * Retrieve the current excuse settings.
     *
     * @return ExcuseSetting
     */
    public static function current()
    {
        return self::first() ?? self::create();
    }
}
