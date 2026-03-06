<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FacilityLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'facility_id',
        'action',
        'log_values',
        'log_timestamp',
        'created_by',
    ];

    protected $casts = [
        'log_values' => 'array',
        'log_timestamp' => 'datetime',
    ];

    public function facility()
    {
        return $this->belongsTo(Facility::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
