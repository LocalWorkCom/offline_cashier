<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashierSettingLog extends Model
{
    use HasFactory;

    // Table name (optional if naming doesn't follow conventions)
    protected $table = 'cashier_setting_log';

    // Mass-assignable attributes
    protected $fillable = [
        'employee_id',
        'branch_id',
        'pos_id',
        'min_total',
        'max_total',
        'min_num',
        'max_num',
        'auto_run_time',
        'created_by',
        'modified_by',
        'deleted_by',
        'created_by_type',
        'modified_by_type',
        'deleted_by_type',
    ];

    /**
     * Relationship: CashierSettingLog belongs to an Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Relationship: CashierSettingLog belongs to a Branch
     */
    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    /**
     * Relationship: CashierSettingLog belongs to a POS
     */
    public function pos()
    {
        return $this->belongsTo(CashierMachine::class, 'pos_id');
    }

    /**
     * Relationship: CashierSettingLog belongs to the User who created it
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
