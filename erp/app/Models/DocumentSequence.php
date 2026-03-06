<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentSequence extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('document-sequence');
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
        'document_type_id',
        'prefix',
        'suffix',
        'numbering_style',
        'format', //01,001,0001
        'include_year',
        'year_format',
        'include_branch',
        'branch_id',
        'current_sequence',
        'created_by',
        'updated_by',
        'active',
    ];

    protected $casts = [
        'include_year'   => 'boolean',
        'include_branch' => 'boolean',
        'active'         => 'boolean',
    ];

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    public function documentType()
    {
        return $this->belongsTo(DocumentNameType::class, 'document_type_id');
    }
    public function formats()
    {
        return $this->hasMany(DocumentFormat::class, 'document_sequnce_id');
    }
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
}
