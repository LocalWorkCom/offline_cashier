<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use Illuminate\Support\Facades\File;

use Illuminate\Database\Eloquent\Model;

class Permission extends SpatiePermission
{
    protected $appends = ['name_en', 'name_ar'];

    public function getNameEnAttribute()
    {
        return $this->getTranslationValue('en');
    }

    public function getNameArAttribute()
    {
        return $this->getTranslationValue('ar');
    }

    private function getTranslationValue($locale)
    {
        $filePath = resource_path("lang/{$locale}/permissions.php");

        if (!File::exists($filePath)) {
            return $this->name; // fallback to key if file missing
        }

        $translations = include $filePath;

        // return translated value or fallback to key name
        return $translations[$this->name] ?? $this->name;
    }
    public function modules()
    {
        return $this->belongsToMany(
            \App\Models\SystemModule::class,
            'module_permission',
            'permission_id',
            'module_id'
        );
    }
    public function group()
    {
        return $this->belongsTo(PermissionGroup::class, 'group_id');
    }


    protected $hidden = ['pivot'];
}
