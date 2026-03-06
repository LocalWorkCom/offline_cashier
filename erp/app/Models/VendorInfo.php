<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class VendorInfo extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('vendor-info');
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
        'vendor_id',
        'tax_card_number',
        'commercial_registration_number',
        'contact_name',
        'contact_phone',
                'country_code',

        'contact_email',
    ];

    /*--------------------------------------
    | Relationships
    ---------------------------------------*/

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }
}
