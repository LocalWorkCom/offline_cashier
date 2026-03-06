<?php

namespace App\Models;


use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    public function modules()
    {
        return $this->belongsToMany(SystemModule::class, 'role_modules', 'role_id', 'module_id');
    }
    public function module()
{
    return $this->belongsTo(SystemModule::class, 'module_id');
}

}
