<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class PoMergeLog extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch');
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

    protected $table = 'po_merge_logs';

    protected $fillable = [
        'merged_po_id',
        'old_po_ids',
        'linked_prs',
        'items',
        'deals',
        'created_by',
    ];

    protected $casts = [
        'old_po_ids' => 'array',
        'linked_prs' => 'array',
        'items'      => 'array',
        'deals'      => 'array',
    ];

    public function mergedPO()
    {
        return $this->belongsTo(PurchaseOrder::class, 'merged_po_id');
    }

    public function created_by()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
