<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RolePermissionLog extends Model
{
    use HasFactory;
    protected $fillable = [
        'causer_id',
        'causer_type',
        'action',
        'employee_id',
        'role_id',
        'permission_ids',
        'extra_data',
    ];

    protected $casts = [
        'permission_ids' => 'array', // auto-cast JSON to array
        'extra_data'     => 'array',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * The role assigned or removed.
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(\Spatie\Permission\Models\Role::class, 'role_id');
    }

    /**
     * The user/employee/admin who caused this action.
     * `causer_type` allows polymorphic relation.
     */
    public function causer()
    {
        return $this->morphTo();
    }

    /**
     * Get permission models from stored IDs.
     */
    public function permissions()
    {
        return \Spatie\Permission\Models\Permission::whereIn('id', $this->permission_ids ?? [])->get();
    }
}
