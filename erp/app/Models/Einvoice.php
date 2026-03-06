<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class Einvoice extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('einvoice');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = "einvoices";
    protected $fillable = ["invoice_type", "invoice_id"];
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id', 'id');
    }


    // public function order()
    // {
    //     return $this->belongsTo(Order::class, 'order_id'); // ✅ Make sure 'order_id' exists in `einvoices` table
    // }
}
