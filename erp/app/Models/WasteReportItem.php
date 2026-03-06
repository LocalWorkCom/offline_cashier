<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class WasteReportItem extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('zone');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'waste_report_items';

    protected $fillable = [
        'waste_report_id',
        'product_brand_id',
        'unit_id',
        'waste_reason_id',
        'waste_unit_id',
        'quantity',
        'actual_quantity_wasted',
        'status',
        'image',
        'reviewed_employee_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    // Relationships

    public function wasteReport()
    {
        return $this->belongsTo(WasteReport::class, 'waste_report_id');
    }

    public function productBrand()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    public function wasteReason()
    {
        return $this->belongsTo(WasteReason::class, 'waste_reason_id');
    }

    public function wasteUnit()
    {
        return $this->belongsTo(Unit::class, 'waste_unit_id');
    }

    public function reviewedEmployee()
    {
        return $this->belongsTo(Employee::class, 'reviewed_employee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }
}
