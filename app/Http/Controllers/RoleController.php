<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(): View
    {
        $roles = Role::withCount(['users', 'permissions'])
            ->with(['permissions'])
            ->orderBy('id')
            ->get();

        $allPermissionsCount = Permission::count();

        return view('roles.index', compact('roles', 'allPermissionsCount'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name',
            'description' => 'nullable|string|max:255',
        ]);

        $slug = Str::slug($validated['name']);
        // Ensure slug uniqueness
        $count = Role::where('slug', 'like', "{$slug}%")->count();
        if ($count > 0) {
            $slug .= '-' . ($count + 1);
        }

        $role = Role::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
            'is_system' => false,
        ]);

        return redirect()->route('roles.edit', $role)->with('success', "Role '{$role->name}' created. Now select permissions.");
    }

    /**
     * Show the form for editing the specified role and its permissions matrix.
     */
    public function edit(Role $role): View
    {
        $role->load('permissions');

        // Group permissions by module
        $modules = Permission::orderBy('module')->orderBy('id')->get()->groupBy('module');

        $rolePermissionIds = $role->permissions->pluck('id')->toArray();

        return view('roles.edit', compact('role', 'modules', 'rolePermissionIds'));
    }

    /**
     * Update the specified role and permissions in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:roles,name,' . $role->id,
            'description' => 'nullable|string|max:255',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        // If system role, keep original slug; otherwise update slug
        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        if (!$role->is_system) {
            $data['slug'] = Str::slug($validated['name']);
        }

        $role->update($data);

        // Sync permissions
        $permissionIds = $request->input('permissions', []);
        $role->permissions()->sync($permissionIds);

        return redirect()->route('roles.index')->with('success', "Role '{$role->name}' permissions updated successfully.");
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return redirect()->route('roles.index')->with('error', "System role '{$role->name}' is protected and cannot be deleted.");
        }

        if ($role->users()->count() > 0) {
            return redirect()->route('roles.index')->with('error', "Cannot delete role '{$role->name}' because it is assigned to {$role->users()->count()} member(s). Reassign them first.");
        }

        $roleName = $role->name;
        $role->delete();

        return redirect()->route('roles.index')->with('success', "Role '{$roleName}' deleted successfully.");
    }
}
