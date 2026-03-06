<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class DepositRuleVendor  extends Model
{
    use HasFactory, LogsActivity;
  
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('deposit_rule_vendor');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

   use HasFactory;

    protected $table = 'deposit_rule_vendor';

    protected $fillable = [
        'deposit_rule_id',
        'vendor_id',
    ];

    // Relations
    public function depositRule()
    {
        return $this->belongsTo(DepositRule::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
