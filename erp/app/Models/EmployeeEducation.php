<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
class EmployeeEducation extends Model
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('employee-education');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }
    protected $table = 'employee_educations';

    protected $fillable = [
        'employee_id',
        'university_id',
        'filed_of_study_id',
        'education_level_id',
        'graduation_year',
        'degree_certificate',
        'additional_certifications',
        'certification_documents',
        'degree',
        'major',
        'start_date',
        'end_date'
    ];


    public function university()
    {
        return $this->belongsTo(University::class, 'university_id');
    }
  
    public function education_level()
    {
        return $this->belongsTo(EducationLevel::class, 'education_level_id');
    }
    public function filed_of_study()
    {
        return $this->belongsTo(FiledOfStudy::class, 'filed_of_study_id');
    }
     public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }
}
