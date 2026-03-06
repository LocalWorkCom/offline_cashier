<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SystemModule extends Model
{
    use HasFactory;

    protected $table = 'system_modules';
  public function permissions()
{
    return $this->belongsToMany(
        \App\Models\Permission::class,
        'module_permission',
        'module_id',
        'permission_id'
    );
}


    public function roles()
    {
        return $this->hasMany(Role::class, 'module_id');
    }
}
