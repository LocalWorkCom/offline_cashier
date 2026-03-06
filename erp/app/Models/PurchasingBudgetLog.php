<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchasingBudgetLog extends Model
{
    use HasFactory;

    protected $table = 'purchasing_budget_logs';
    protected $fillable = [
        'purchasing_budget_id',
        'amount',
        'type',
        'reference',
        'reasone',
        'prch_manager_notify',
        'previous_remaining',
        'new_remaining',
        'created_by',
    ];


    public function budget()
    {
        return $this->belongsTo(PurchasingBudget::class, 'purchasing_budget_id');
    }

    /**
     * employee who created this log
     */
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }
}
