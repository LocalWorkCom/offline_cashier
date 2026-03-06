<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditItem extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'audit_id',
        'product_brand_id',
        'unit_id',
        'system_qty',
        'actual_qty',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
        'reason_id',
        'barcode',
    ];

    protected $casts = [
        'system_qty' => 'float',
        'actual_qty' => 'float',
    ];

    /**
     * Relationships
     */
    public function audit()
    {
        return $this->belongsTo(Audit::class, 'audit_id');
    }

    public function productBrand()
    {
        return $this->belongsTo(ProductBrand::class, 'product_brand_id');
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }
    public function reason()
    {
        return $this->belongsTo(DiscrepancyReason::class, 'reason_id');
    }

    /**
     * Accessors / Mutators (optional)
     */
    public function getVarianceAttribute()
    {
        if (is_null($this->actual_qty)) {
            return null;
        }
        return $this->actual_qty - $this->system_qty;
    }
}
