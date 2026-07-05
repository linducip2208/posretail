<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    /**
     * @return Collection<int, Permission>
     */
    public function permissions(): Collection
    {
        return $this->roles()->with('permissions')->get()
            ->pluck('permissions')
            ->flatten()
            ->unique('id');
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions()->contains('slug', $slug);
    }

    public function hasAnyPermission(array $slugs): bool
    {
        return $this->permissions()->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function hasAllPermissions(array $slugs): bool
    {
        $userSlugs = $this->permissions()->pluck('slug')->toArray();
        return empty(array_diff($slugs, $userSlugs));
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles->contains('slug', $slug);
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles->whereIn('slug', $slugs)->isNotEmpty();
    }

    public function assignRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    public function removeRole(string|Role $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }
        $this->roles()->detach($role->id);
    }

    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
    }

    /**
     * Get permission slugs grouped by group name.
     */
    public function groupedPermissions(): Collection
    {
        return $this->permissions()->groupBy('group');
    }

    /**
     * Check if user can access a navigation group (has at least 'view' permission in that group).
     */
    public function canAccessGroup(string $group): bool
    {
        if ($this->hasPermission('*')) {
            return true;
        }
        return $this->hasPermission("view-{$group}");
    }

    /**
     * Check if user can perform an action on a resource.
     */
    public function canAccess(string $group, string $action = 'view'): bool
    {
        if ($this->hasPermission('*')) {
            return true;
        }
        return $this->hasPermission("{$action}-{$group}");
    }
}
