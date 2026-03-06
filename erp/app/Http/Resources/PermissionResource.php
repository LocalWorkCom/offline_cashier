<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray($request)
    {
        // Role itself
        $role = $this->resource;
        $assigned = $role->permissions->pluck('id')->toArray();

        // Get all available permissions for this role
        $permissions = \Spatie\Permission\Models\Permission::query()
            ->where('guard_name', $role->guard_name)
            ->where('is_active', 0)
            ->get();

        // Exclude some permissions for specific roles
        if (in_array($role->name, ['Kitchen Manager', 'Branch Manager'])) {
            $permissions = $permissions->reject(fn($p) => in_array($p->name, [
                'view einvoices',
                'view einvoice_settings',
                'create einvoice_settings',
                'update einvoice_settings',
                'view officer_assign_setting',
                'create officer_assign_setting',
                'update officer_assign_setting',
                'view einvoices_superadmin',
            ]));
        }

        // Build response
        $permissionsResponse = $permissions
            ->map(function ($p) use ($assigned) {
                $parts = explode(' ', $p->name);
                return [
                    'id'        => $p->id,
                    'name'      => $p->name,
                    'is_active' => $p->is_active,
                    'assigned'  => in_array($p->id, $assigned),
                    'category'  => ucfirst($parts[1] ?? 'Others'),
                ];
            })
            ->groupBy('category')
            ->map(fn($items, $category) => [
                'category' => $category,
                'items'    => $items->map(fn($i) => Arr::except($i, ['category']))->values(),
            ])
            ->values();

        return [
            'id'   => $role->id,
            'name' => $role->name,
            'permissions' => $permissionsResponse,
        ];
    }
}
