<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalLog extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'journal_id',
        'action',
        'log_values',
        'log_timestamp',
        'created_by',
    ];

    protected $casts = [
        'log_values' => 'array',
        'log_timestamp' => 'datetime',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
