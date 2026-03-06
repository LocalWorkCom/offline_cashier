<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class BranchSafe extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('branch-safe');
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
    protected $table = 'branch_safe';


    // Fillable fields
    protected $fillable = [
        'cashier_id',
        'branch_id',
        'balances_ids',
        'cash_amount',
        'visa_amount',
        'reason',
        'cashier_machine_id',
        'deficit_cash',
        'deficit_visa',
        'deleted_by',
        'created_by',
        'updated_by'
    ];

    protected $casts = [
        'balances_ids' => 'array',
    ];


    // Soft delete
    protected $dates = ['deleted_at'];


    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
    public function machine()
    {
        return $this->belongsTo(CashierMachine::class, 'cashier_machine_id');
    }


    public function employee()
    {
        return $this->belongsTo(Employee::class, 'cashier_id');
    }
    /**
     * Get the user who created the region.
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    /**
     * Get the user who last updated the region.
     */
    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
}
