<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Audit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'audit_number',
        'audit_type',
        'period',
        'from_date',
        'to_date',
        'audit_date',
        'scope',
        'scope_type',
        'scope_ids',
        'status',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
        'responsible_user_id',
    ];

    protected $casts = [
        'scope_ids' => 'array',
    ];

    // protected static function boot()
    // {
    //     parent::boot();

    //     static::creating(function ($audit) {
    //         // Generate auto number only if not provided manually
    //         if (empty($audit->audit_number)) {
    //             $latest = static::withTrashed()->max('id') + 1;
    //             $audit->audit_number =  str_pad($latest, 5, '0', STR_PAD_LEFT);
    //         }
    //     });
    // }

    public function items()
    {
        return $this->hasMany(AuditItem::class);
    }

    /**
     * Scope helpers
     */

    public function scopePeriodic($query)
    {
        return $query->where('audit_type', 'periodic');
    }

    public function scopeSurprise($query)
    {
        return $query->where('audit_type', 'surprise');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'Done');
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'responsible_user_id');
    }
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
    public function zone()
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }
}
