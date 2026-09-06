<?php

namespace App\Traits;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRolesAndPermissions
{
    /**
     * The roles that belong to the user.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')->withTimestamps();
    }

    /**
     * Direct permissions assigned to user.
     */
    public function directPermissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user')
            ->withPivot('type')
            ->withTimestamps();
    }

    /**
     * Check if user has a specific role or any of the given roles.
     *
     * @param string|array $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        return $this->roles->contains('slug', $roles);
    }

    /**
     * Assign role to user.
     */
    public function assignRole(Role|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->firstOrFail();
        }

        if (!$this->roles->contains($role->id)) {
            $this->roles()->attach($role->id);
            $this->load('roles');
        }
    }

    /**
     * Remove role from user.
     */
    public function removeRole(Role|string $role): void
    {
        if (is_string($role)) {
            $role = Role::where('slug', $role)->first();
            if (!$role) return;
        }

        $this->roles()->detach($role->id);
        $this->load('roles');
    }

    /**
     * Sync user roles.
     */
    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
        $this->load('roles');
    }

    /**
     * Check if user is Super Admin.
     */
    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super-admin');
    }

    /**
     * Check if user has a given permission (via roles or direct grants).
     */
    public function hasPermission(string $permissionSlug): bool
    {
        // 1. Super admin always has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // 2. Check direct denial
        $denied = $this->directPermissions
            ->where('slug', $permissionSlug)
            ->where('pivot.type', 'deny')
            ->isNotEmpty();

        if ($denied) {
            return false;
        }

        // 3. Check direct grant
        $granted = $this->directPermissions
            ->where('slug', $permissionSlug)
            ->where('pivot.type', 'grant')
            ->isNotEmpty();

        if ($granted) {
            return true;
        }

        // 4. Check permissions inherited from roles
        return $this->roles->flatMap->permissions->contains('slug', $permissionSlug);
    }

    /**
     * Get all effective permissions for user.
     */
    public function getAllPermissions(): Collection
    {
        if ($this->isSuperAdmin()) {
            return Permission::all();
        }

        return $this->roles->flatMap->permissions->unique('id');
    }

    /**
     * Get primary role.
     */
    public function getPrimaryRoleAttribute(): ?Role
    {
        return $this->roles->first();
    }
}
