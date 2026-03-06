<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'rate',
        'rate_comment',
        'effective_date',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $dates = [
        'effective_date',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'deleted_by',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $appends = ['degree'];
    public function getDegreeAttribute()
    {
        $lang = request()->header('lang', 'en');

        $map = [
            'ar' => [
                1 => 'ضعيف',
                2 => 'متوسط',
                3 => 'جيد',
                4 => 'جيد جداً',
                5 => 'ممتاز',
            ],
            'en' => [
                1 => 'Poor',
                2 => 'Fair',
                3 => 'Good',
                4 => 'Very Good',
                5 => 'Excellent',
            ]
        ];

        return $map[$lang][(int)$this->rate] ?? null;
    }
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id', 'id');
    }
}
