<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntryDetails extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'journal_entry_id',
        'journal_id',
        'cost_center_id',
        'customer_id',
        'supplier_id',
        'credit',
        'debit',
        'current_credit',
        'current_debit',
        'description',
        'is_active',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class, 'journal_id');
    }

    public function journalEntry()
    {
        return $this->belongsTo(JournalEntry::class, 'journal_entry_id');
    }

    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class, 'cost_center_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function createdBy()
    {
        if ($this->created_by_type === 'employee') {
            return $this->belongsTo(Employee::class, 'created_by');
        }

        if ($this->created_by_type === 'user') {
            return $this->belongsTo(User::class, 'created_by');
        }

        return $this->belongsTo(Employee::class, 'created_by')->withDefault();
    }
}

