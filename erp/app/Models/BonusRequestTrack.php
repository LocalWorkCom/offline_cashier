<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BonusRequestTrack extends Model
{
    protected $fillable = [
        'bonus_request_id',
        'status',
        'reason',
        'payout_date',
        'created_by',
    ];

    public function bonusRequest()
    {
        return $this->belongsTo(BonusRequest::class);
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
