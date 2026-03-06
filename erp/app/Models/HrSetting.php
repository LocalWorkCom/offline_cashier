<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HrSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'duplicate_punch_threshold_minutes',
    ];

    protected $casts = [
        'duplicate_punch_threshold_minutes' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public static function getDuplicatePunchThreshold($branchId)
    {
        if (!$branchId) {
            return 5; // Default 5 minutes if no branch provided
        }
        
        $setting = self::where('branch_id', $branchId)->first();
        return $setting ? $setting->duplicate_punch_threshold_minutes : 5; // Default 5 minutes
    }
}
