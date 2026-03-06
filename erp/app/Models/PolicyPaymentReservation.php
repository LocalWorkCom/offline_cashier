<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class PolicyPaymentReservation extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('policy-payment-reservation');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $table = 'policies_payment_reservation';

    protected $fillable = [
        'payment_ar',
        'payment_en',
        'reservation_ar',
        'reservation_en',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    protected $appends = ['payment', 'reservation'];

    // public function getNameSiteAttribute($value){
    //     return app()->getLocale() === 'en' ? $this->name_en : $this->name_ar;
    // }

    // public function getDescriptionSiteAttribute($value){
    //     return app()->getLocale() === 'en' ? $this->reservation_en : $this->reservation_ar;
    // }

    public function getPaymentAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->payment_en : $this->payment_ar;
    }
    public function getReservationAttribute()
    {
        return app()->getLocale() === 'en' ? $this->reservation_en : $this->reservation_ar;
    }


}
