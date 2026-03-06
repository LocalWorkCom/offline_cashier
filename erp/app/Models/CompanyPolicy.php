<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class CompanyPolicy extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('company-policy');
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
        'company_id',
        'title',
        'description',
        'file_path',
        'version',
        'is_active',
        'created_by',
        'updated_by'
    ];

    public function company()
    {
        return $this->belongsTo(CompanyProfileSetting::class, 'company_id');
    }
    public function createdBy()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function updatedBy()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
    public function acknowledgements()
    {
        return $this->hasMany(CompanyPolicyAcknowledgement::class, 'company_policy_id');
    }
}
