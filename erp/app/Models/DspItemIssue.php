<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DspItemIssue extends Model
{
    use HasFactory, softDeletes;

    protected $fillable = [
        'dsp_item_id',
        'issue_type_id',
        'unit_id',
        'quantity',
        'notes',
        'created_by',
        'created_by_type',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
        'created_by',
        'modified_by',
        'deleted_by',
    ];

    /**
     * 🔗 Each issue has one issue type.
     */
    public function issueType()
    {
        return $this->belongsTo(DirectSupplyIssueType::class, 'issue_type_id');
    }
    public function item()
    {
        return $this->belongsTo(DirectSupplyPermissionItem::class, 'dsp_item_id');
    }
    /**
     * 🔗 Each issue may have one unit.
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * 🔗 Polymorphic relation to creator (employee or user).
     */
    public function creator()
    {
        return $this->morphTo(__FUNCTION__, 'created_by_type', 'created_by');
    }
}
