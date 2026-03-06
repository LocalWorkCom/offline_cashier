<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectSupplyPermission extends Model
{
    protected $fillable = [
        'date',
        'dsp_no',
        'received_unit_id',
        'received_quantity',
        'from_store_id',
        'to_store_id',
        'department',
        'linked_pr_id',
        'linked_so_id',
        'qa_tester_id',
        'created_by',
        'dsp_status_id',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type'
    ];

    public function items()
    {
        return $this->hasMany(DirectSupplyPermissionItem::class, 'dsp_id');
    }

    public function creator()
    {
        return $this->belongsTo(Employee::class, 'created_by');
    }

    public function qaTester()
    {
        return $this->belongsTo(Employee::class, 'qa_tester_id');
    }
    public function fromStore()
    {
        return $this->belongsTo(Store::class, 'from_store_id');
    }

    // To store
    public function toStore()
    {
        return $this->belongsTo(Store::class, 'to_store_id');
    }
    //Linked Purchase Request
    public function purchaseRequest()
    {
        return $this->belongsTo(PurchaseRequest::class, 'linked_pr_id');
    }
    // Status
    public function status()
    {
        return $this->belongsTo(DirectSupplyPermissionStatusSetting::class, 'dsp_status_id');
    }

    //Linked Supply Order
    public function supplyOrder()
    {
        return $this->belongsTo(SupplyOrder::class, 'linked_so_id');
    }
    public function logs()
    {
        return $this->hasMany(DirectSupplyPermissionLog::class, 'dsp_id');
    }
       public function returns()
    {
        return $this->hasMany(ReturnDsp::class, 'dsp_id');
    }
}
