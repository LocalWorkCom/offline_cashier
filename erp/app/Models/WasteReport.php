<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class WasteReport extends Model
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
    protected $table = 'waste_reports';

    protected $fillable = [
        'store_id',
        'report_number',
        'name',
        'date',
        'time',
        'status',
        'employee_id',
        'first_quality_officer_id',
        'second_quality_officer_id',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $dates = [
        'date',
        'time',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


    //Store relation
    public function store()
    {
        return $this->belongsTo(Store::class, 'store_id');
    }

    //Creator employee (the one who made the report)
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    //First quality officer
    public function firstQualityOfficer()
    {
        return $this->belongsTo(Employee::class, 'first_quality_officer_id');
    }

    //Second quality officer
    public function secondQualityOfficer()
    {
        return $this->belongsTo(Employee::class, 'second_quality_officer_id');
    }

    //Created by user (if different from employee)
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    //Updated by user
    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }

    //Deleted by user
    public function deletedBy()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }

    public function items()
    {
        return $this->hasMany(WasteReportItem::class, 'waste_report_id');
    }


    public function getStatusLabelAttribute(): string
    {
        $lang = request()->header('lang', 'ar');
        $translations = [
            'pending' => ['ar' => 'معلق', 'en' => 'Pending'],
            'Being Audited' => ['ar' => 'جارى المراجعه', 'en' => 'Being Audited'],
            'confirmed' => ['ar' => 'تم الموافقه', 'en' => 'Confirmed'],
            'rejected' => ['ar' => 'مرفوض', 'en' => 'Rejected'],
        ];

        return $translations[$this->status][$lang] ?? ($lang === 'ar' ? 'غير معروف' : 'Unknown');
    }
}
