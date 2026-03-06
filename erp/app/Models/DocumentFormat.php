<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

class DocumentFormat extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->useLogName('document-format');
    }
    public function tapActivity(Activity $activity, string $eventName)
    {
        $user = getAuthenticatedUser();
        if ($user) {
            $activity->causer_id = $user->id;
            $activity->causer_type = get_class($user);
        }
    }

    protected $appends = ['header_text', 'footer_text'];

    protected $fillable = [
        'document_sequnce_id',
        'logo_location',
        'header_text_en',
        'header_text_ar',
        'footer_text_en',
        'footer_text_ar',
        'font_type',
        'font_size',
        'compliance_text_ar',
        'compliance_text_en',
        'signeter_count',
        'created_by',
        'updated_by',
        'active',
    ];

    protected $casts = [
        'values_signeter' => 'array',
        'active'          => 'boolean'
    ];
    public function getHeaderAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->header_text_en : $this->header_text_ar;
    }

    public function getFooterAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->footer_text_en : $this->footer_text_ar;
    }
    public function getComplianceAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->compliance_text_en : $this->compliance_text_ar;
    }
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(Employee::class, 'updated_by');
    }
    public function signeters()
    {
        return $this->hasMany(DocumentFormatSigneter::class);
    }
}
