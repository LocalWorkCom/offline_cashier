<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DirectSupplyPermissionStatusSetting extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $appends = ['name', 'description'];
    protected $fillable = [
        'name_ar',
        'name_en',
        'position',
        'description_ar',
        'description_en',
        'type',
        'previous_statuses',
        'next_statuses',
        'behavior',
        'active',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
        'created_at',
        'modified_at'
    ];

    protected $casts = [
        'previous_statuses' => 'array',
        'next_statuses' => 'array',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'modified_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    protected $hidden = [
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
        'created_at',
        'modified_at',
        'deleted_at',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    // Scopes (Updated)
    public function scopeFinal($query)
    {
        return $query->where('type', 'final');
    }

    public function scopeStart($query)
    {
        return $query->where('type', 'start');
    }

    public function scopeIntermediate($query)
    {
        return $query->where('type', 'intermediate');
    }

    public function scopeManual($query)
    {
        return $query->where('behavior', 'manual');
    }

    public function scopeAutomatic($query)
    {
        return $query->where('behavior', 'automatic');
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    // Helper Methods
    public function isStartStatus()
    {
        return $this->type === 'start';
    }

    public function isFinalStatus()
    {
        return $this->type === 'final';
    }

    public function isIntermediateStatus()
    {
        return $this->type === 'intermediate';
    }

    public function isManual()
    {
        return $this->behavior === 'manual';
    }

    public function isAutomatic()
    {
        return $this->behavior === 'automatic';
    }

    public function getAllowedPreviousStatuses()
    {
        return $this->previous_statuses ?? [];
    }

    public function getAllowedNextStatuses()
    {
        return $this->next_statuses ?? [];
    }

    public function canTransitionTo($statusId)
    {
        return in_array($statusId, $this->getAllowedNextStatuses());
    }

    public function canTransitionFrom($statusId)
    {
        return in_array($statusId, $this->getAllowedPreviousStatuses());
    }

    // Name accessor with fallback
 
    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->name_en : $this->name_ar;
    }

    // Description accessor with fallback
    public function getDescriptionAttribute()
    {
        return request()->header('lang', 'ar') === 'en' ? $this->description_en : $this->description_ar;
    }
}