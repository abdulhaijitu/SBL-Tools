<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request): View
    {
        $query = User::with(['roles'])->withCount(['leads', 'tasks']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $roleSlug = $request->input('role');
            $query->whereHas('roles', function ($q) use ($roleSlug) {
                $q->where('slug', $roleSlug);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $users = $query->orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->filled('role_id')) {
            $role = Role::find($request->input('role_id'));
            if ($role) {
                $this->authorizeRole($role);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'email' => 'nullable|string|email|max:255|unique:users,email',
            'designation' => 'nullable|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'password' => 'required|string|min:6',
            'status' => 'nullable|in:active,inactive',
        ]);

        $phone = $validated['phone'] ?? null;
        if (empty($phone)) {
            $phone = '01' . rand(300000000, 999999999);
        }

        $email = $validated['email'] ?? null;
        if (empty($email)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
            $email = ($cleanPhone ?: 'user_' . time()) . '@sbl.test';
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $phone,
            'designation' => $validated['designation'] ?? null,
            'status' => $validated['status'] ?? 'active',
            'password' => Hash::make($validated['password']),
        ]);

        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('users.index')->with('success', "সিস্টেম ইউজার '{$user->name}' সফলভাবে তৈরি হয়েছে। মোবাইল: {$user->phone}");
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:users,phone,' . $user->id,
            'email' => 'nullable|string|email|max:255|unique:users,email,' . $user->id,
            'designation' => 'nullable|string|max:100',
            'status' => 'required|in:active,inactive',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6',
        ]);

        $role = Role::findOrFail($validated['role_id']);
        $this->authorizeRole($role, $user);
        if ($user->id === $request->user()->id && ($validated['status'] !== 'active' || ($user->isSuperAdmin() && $role->slug !== 'super-admin'))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['role_id' => 'Ask another administrator to change your administrative access.']);
        }

        $email = $validated['email'] ?? null;
        if (empty($email)) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $validated['phone']);
            $email = ($cleanPhone ?: 'user_' . $user->id) . '@sbl.test';
        }

        $data = [
            'name' => $validated['name'],
            'email' => $email,
            'phone' => $validated['phone'],
            'designation' => $validated['designation'] ?? null,
            'status' => $validated['status'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);
        $user->roles()->sync([$validated['role_id']]);

        return redirect()->route('users.index')->with('success', "User '{$user->name}' updated successfully.");
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->isSuperAdmin() && !auth()->user()->isSuperAdmin(), 403);
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }

        if ($user->email === 'admin@sbl.test') {
            return redirect()->route('users.index')->with('error', 'The primary administrator account cannot be deleted.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('users.index')->with('success', "Team member '{$userName}' deleted successfully.");
    }

    private function authorizeRole(Role $role, ?User $target = null): void
    {
        if (auth()->user()->isSuperAdmin()) return;
        abort_if($role->slug === 'super-admin' || $target?->isSuperAdmin(), 403);
        // Delegated managers may only assign permissions they themselves hold.
        foreach ($role->permissions as $permission) {
            abort_unless(auth()->user()->hasPermission($permission->slug), 403);
        }
    }
}
