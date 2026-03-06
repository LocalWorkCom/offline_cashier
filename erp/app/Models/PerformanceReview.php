<?php

namespace App\Models;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PerformanceReview extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id',
        'rating',
        'strengths',
        'weaknesses',
        'additional_comments',
        'created_by',
        'created_at',
        'created_by_type',
    ];
    protected $hidden = [
        'updated_at',
        'deleted_at',
        'modified_by',
        'deleted_by',
        'modified_by_type',
        'deleted_by_type'
    ];
    protected $casts = [
        'rating' => 'integer',
    ];
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
