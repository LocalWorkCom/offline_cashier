<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name_ar',
        'name_en',
        // 'description_en',
        'description_ar',
        'is_active',
        'code',
        'account_type',
        'parent_id',
        'currency_id',
        'facility_id',
        'type',
        'level',
        'debit',
        'credit',
        'balance',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    protected $casts = [
        // 'is_payment_account' => 'boolean',
        // 'is_active' => 'boolean',
        // 'open_balance' => 'decimal:2',
        'debit'   => 'decimal:2',
        'credit'  => 'decimal:2',
        'balance' => 'decimal:2'
    ];

    // protected $appends = ['last_child'];

    protected $appends = ['name'];

    public function parent()
    {
        return $this->belongsTo(Journal::class, 'parent_id');
    }

    public function facility()
    {
        return $this->belongsTo(Facility::class, 'facility_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

    public function openBalanceJournal(): BelongsTo
    {
        return $this->belongsTo(Journal::class, 'open_balance_id');
    }

    public function children()
    {
        return $this->hasMany(Journal::class, 'parent_id', 'id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    public function journalEntryDetails()
    {
        return $this->hasMany(JournalEntryDetails::class, 'journal_id', 'id');
    }

    public function hasChildren()
    {
        return $this->children()->exists();
    }

    //ToDo check if the journal has transactions after we implement the logic
    public function hasTransactions()
    {
        return false;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeArchive($query)
    {
        return $query->where('is_active', 2);
    }

    public function getNameAttribute()
    {
        return request()->header('lang', 'ar') === 'ar' ? $this->name_ar : $this->name_en;
    }

    public function getDescriptionAttribute()
    {
        return request()->header('lang', 'ar') === 'ar' ? $this->description_ar : $this->description_en;
    }

    public function getLevelAttribute(): int
    {
        $level = 1;
        $parent = $this->parent;

        while ($parent) {
            $level++;
            $parent = $parent->parent;
        }
        return $level;
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function modifier()
    {
        return $this->belongsTo(Employee::class, 'modified_by');
    }

    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'deleted_by');
    }

    public function logs()
    {
        return $this->hasMany(JournalLog::class);
    }
    public function canDelete(): bool
    {
        return !$this->hasTransactions() && !$this->hasChildren();
    }

    public static function generateCodeAndLevel($parentId = null, $code = null)
    {
        if (!$parentId) {
            $last = self::whereNull('parent_id')->max('code');
            return [
                'code'  => $last ? $last + 1 : 1,
                'level' => 1
            ];
        }

        $parent = self::findOrFail($parentId);
        $lastChild = self::where('parent_id', $parentId)
            ->orderByRaw('code DESC')
            ->first();

        if (!$lastChild) {
            $newCode = $parent->code . '01';
        } else {
            $prefix = $parent->code;
            $lastTwoDigits = substr($lastChild->code, -2);
            if(!$code){
                $nextNumber = (int)$lastTwoDigits + 1;
            }else{
                $nextNumber = (int)$code;
            }
            $newCode = $prefix . str_pad($nextNumber, 2, '0', STR_PAD_LEFT);
        }

        return [
            'code'  => $newCode,
            'level' => $parent->level + 1
        ];
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


