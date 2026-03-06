<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JournalEntry extends Model
{
    use HasFactory, SoftDeletes;

        protected $fillable = [
        'currency_id',
        'facility_id',
        'journal_entry_department_id',
        'journal_entry_numner',
        'ledger_number',
        'account_type',
        'date',
        'description',
        'file',
        'is_repeated',
        'repeated_count',
        'repeated_type',
        'total_debit',
        'total_credit',
        'current_total_debit',
        'current_total_credit',
        'status',
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

    protected $appends = ['total_credit', 'total_debit', 'balance'];

    public function getTotalCreditAttribute($value){
        return $this->journalEntryDetails->sum('credit');
    }

    public function getTotalDebitAttribute($value){
        return $this->journalEntryDetails->sum('debit');
    }

    public function getBalanceAttribute($value){
        return $this->total_debit - $this->total_credit;
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function journalEntryDepartment()
    {
        return $this->belongsTo(JournalEntryDepartment::class, 'journal_entry_department_id', 'id');
    }

    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetails::class, 'journal_entry_id');
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

    public function modifiedBy()
    {
        if ($this->modified_by_type === 'employee') {
            return $this->belongsTo(Employee::class, 'modified_by');
        }

        if ($this->modified_by_type === 'user') {
            return $this->belongsTo(User::class, 'modified_by');
        }

        return $this->belongsTo(Employee::class, 'modified_by')->withDefault();
    }

    public function deletedBy()
    {
        if ($this->deleted_by_type === 'employee') {
            return $this->belongsTo(Employee::class, 'deleted_by');
        }

        if ($this->deleted_by_type === 'user') {
            return $this->belongsTo(User::class, 'deleted_by');
        }

        return $this->belongsTo(Employee::class, 'deleted_by')->withDefault();
    }


}
