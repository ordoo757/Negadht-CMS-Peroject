<?php

namespace App\Modules\User\Traits;

use App\Modules\User\Models\Role;
use App\Modules\User\Models\Permission;

trait HasRoles
{
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withPivot('meta', 'expires_at');
    }

    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        foreach ($this->roles as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function canAccess(string $module, string $action = 'read'): bool
    {
        if ($this->hasRole('super-admin')) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->hasModuleAccess($module, $action)) {
                return true;
            }
        }
        return false;
    }

    public function assignRole(string $roleSlug, array $meta = []): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role) {
            $this->roles()->syncWithoutDetaching([
                $role->id => ['meta' => json_encode($meta)]
            ]);
        }
    }

    public function removeRole(string $roleSlug): void
    {
        $role = Role::where('slug', $roleSlug)->first();
        if ($role) {
            $this->roles()->detach($role->id);
        }
    }

    public function getDashboardLayout(): ?string
    {
        $meta = $this->roles()
            ->wherePivotNotNull('meta')
            ->first()?->pivot?->meta;

        if ($meta) {
            $meta = json_decode($meta, true);
            return $meta['dashboard_layout'] ?? null;
        }
        return null;
    }
}
