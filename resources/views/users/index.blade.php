@extends('layouts.app')

@section('page-title', 'User Management')
@section('page-subtitle', 'Manage team members, roles, and administrative access')

@section('content')
@php
    $currentUserId = auth()->id() ?? 0;
    $currentUserIsSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;

    $usersList = $users->map(function ($u) use ($currentUserId) {
        $primaryRole = $u->primary_role;
        return [
            'id' => $u->id,
            'name' => $u->name,
            'email' => $u->email,
            'phone' => $u->phone,
            'designation' => $u->designation ?: 'Staff Member',
            'status' => $u->status,
            'role_id' => $primaryRole ? $primaryRole->id : null,
            'role_name' => $primaryRole ? $primaryRole->name : 'No Role',
            'role_slug' => $primaryRole ? $primaryRole->slug : '',
            'leads_count' => (int) ($u->leads_count ?? 0),
            'tasks_count' => (int) ($u->tasks_count ?? 0),
            'is_super_admin' => $u->isSuperAdmin(),
            'is_you' => $u->id === $currentUserId,
            'created_at' => $u->created_at ? $u->created_at->format('M d, Y') : null,
        ];
    })->values();

    $rolesList = $roles->map(function ($r) {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'slug' => $r->slug,
            'permissions' => $r->permissions->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'slug' => $p->slug,
                'module' => $p->module,
            ])->values(),
        ];
    })->values();

    $totalCount = $users->count();
    $activeCount = $users->where('status', 'active')->count();
    $staffCount = $users->reject(fn($u) => $u->hasRole('super-admin'))->count();
    $superAdminCount = $users->filter(fn($u) => $u->hasRole('super-admin'))->count();
@endphp

<div class="space-y-6" 
     x-data="userManagement({ 
         users: {{ Js::from($usersList) }}, 
         roles: {{ Js::from($rolesList) }}, 
         currentUserId: {{ $currentUserId }}, 
         isSuperAdmin: {{ $currentUserIsSuperAdmin ? 'true' : 'false' }} 
     })"
     @click="closeMenu()">

    <!-- Top Action Bar & Stat Counters (2x2 on Mobile, 4 in a row on Desktop) -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <!-- Total Users -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex items-center gap-3 transition-all hover:border-orange-300">
            <div class="w-10 h-10 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
                👥
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Total Users</div>
                <div class="text-lg sm:text-2xl font-bold text-slate-900" x-text="counts.total">{{ $totalCount }}</div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex items-center gap-3 transition-all hover:border-emerald-300">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
                🟢
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Active Accounts</div>
                <div class="text-lg sm:text-2xl font-bold text-emerald-600" x-text="counts.active">{{ $activeCount }}</div>
            </div>
        </div>

        <!-- Staff / Team Members -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex items-center gap-3 transition-all hover:border-indigo-300">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
                💼
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Staff & Team</div>
                <div class="text-lg sm:text-2xl font-bold text-indigo-600" x-text="counts.staff">{{ $staffCount }}</div>
            </div>
        </div>

        <!-- Super Admins -->
        <div class="bg-white rounded-2xl p-3 sm:p-4 border border-slate-200/80 shadow-xs flex items-center gap-3 transition-all hover:border-purple-300">
            <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg flex-shrink-0">
                🛡️
            </div>
            <div class="min-w-0 flex-1">
                <div class="text-[11px] sm:text-xs font-semibold text-slate-500 uppercase tracking-wider truncate">Super Admins</div>
                <div class="text-lg sm:text-2xl font-bold text-purple-600" x-text="counts.superAdmins">{{ $superAdminCount }}</div>
            </div>
        </div>
    </div>

    <!-- Filters & Action Header -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3 sm:gap-4">
        
        <!-- Live Search & Dropdowns -->
        <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3">
            <!-- Search Input -->
            <div class="relative flex-1 min-w-[200px]">
                <input aria-label="Search by name, phone, email, designation..." 
                       type="text" 
                       x-model="searchQuery" 
                       placeholder="Search name, phone, email, designation..." 
                       class="w-full pl-9 pr-8 py-2.5 sm:py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3 sm:top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
                <button type="button" 
                        x-show="searchQuery" 
                        @click="searchQuery = ''" 
                        class="absolute right-2.5 top-3 sm:top-2.5 text-slate-400 hover:text-slate-600 text-xs font-bold cursor-pointer p-0.5">✕</button>
            </div>

            <!-- Role Filter -->
            <div class="flex items-center gap-2">
                <select x-model="selectedRole" 
                        aria-label="Filter by Role"
                        class="flex-1 sm:flex-initial text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 sm:py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px] sm:min-h-0 font-medium text-slate-700">
                    <option value="">All Roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->slug }}">{{ $role->name }}</option>
                    @endforeach
                </select>

                <!-- Status Filter -->
                <select x-model="selectedStatus" 
                        aria-label="Filter by Status"
                        class="flex-1 sm:flex-initial text-sm bg-slate-50 border border-slate-200 rounded-xl px-3 py-2.5 sm:py-2 focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px] sm:min-h-0 font-medium text-slate-700">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="inactive">Inactive</option>
                </select>

                <!-- Reset Button -->
                <button type="button" 
                        x-show="searchQuery || selectedRole || selectedStatus" 
                        @click="resetFilters()" 
                        class="text-xs text-orange-600 hover:text-orange-700 font-semibold px-2.5 py-2 hover:bg-orange-50 rounded-lg transition-colors whitespace-nowrap min-h-[44px] sm:min-h-0 flex items-center">
                    Reset
                </button>
            </div>
        </div>

        <!-- Right Side: Live Count & Add User Button -->
        <div class="flex items-center justify-between sm:justify-end gap-3 pt-2 md:pt-0 border-t sm:border-t-0 border-slate-100">
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-600 rounded-lg whitespace-nowrap"
                  x-text="`Showing ${filteredUsers.length} of ${users.length} users`">
                Showing {{ $totalCount }} users
            </span>

            <button type="button" 
                    @click="createModalOpen = true" 
                    class="min-h-[44px] sm:min-h-0 px-4 py-2.5 bg-orange-600 hover:bg-orange-700 active:scale-[0.98] text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2 flex-shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Add User</span>
            </button>
        </div>
    </div>

    <!-- Desktop Table View (Hidden on Mobile, Visible on md:block) -->
    <div class="hidden md:block bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50/80 text-slate-500 uppercase text-[11px] font-semibold border-b border-slate-200/80 tracking-wider select-none">
                <tr>
                    <th scope="col" class="py-3 px-4">User</th>
                    <th scope="col" class="py-3 px-4">Role</th>
                    <th scope="col" class="py-3 px-4">Designation</th>
                    <th scope="col" class="py-3 px-4">Contact</th>
                    <th scope="col" class="py-3 px-4 text-center">Workload</th>
                    <th scope="col" class="py-3 px-4 text-center">Status</th>
                    <th scope="col" class="py-3 px-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <template x-for="user in filteredUsers" :key="user.id">
                    <tr class="hover:bg-slate-50/70 transition-colors group">
                        
                        <!-- User Name & Login -->
                        <td class="py-3 px-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700 flex-shrink-0">
                                    <span x-text="getAvatarLetter(user.name)"></span>
                                </div>
                                <div class="min-w-0">
                                    <div class="font-semibold text-slate-900 flex items-center gap-1.5">
                                        <span class="truncate" x-text="user.name"></span>
                                        <template x-if="user.is_you">
                                            <span class="px-1.5 py-0.5 bg-orange-100 text-orange-800 text-[10px] rounded font-bold uppercase tracking-wide">You</span>
                                        </template>
                                    </div>
                                    <div class="text-xs text-slate-500 font-mono flex items-center gap-1">
                                        <span class="text-slate-400">📱</span>
                                        <span class="font-semibold text-slate-700" x-text="formatPhone(user.phone) || 'No Phone'"></span>
                                    </div>
                                    <template x-if="user.email && !user.email.endsWith('@sbl.test')">
                                        <div class="text-[11px] text-slate-400 truncate max-w-[180px]" x-text="user.email"></div>
                                    </template>
                                </div>
                            </div>
                        </td>

                        <!-- Role Badge -->
                        <td class="py-3 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border"
                                  :class="getRoleColor(user.role_slug)">
                                <span x-text="getRoleBgIcon(user.role_slug)"></span>
                                <span x-text="user.role_name"></span>
                            </span>
                        </td>

                        <!-- Designation -->
                        <td class="py-3 px-4">
                            <span class="text-slate-700 font-medium text-xs sm:text-sm" x-text="user.designation"></span>
                        </td>

                        <!-- Contact Actions -->
                        <td class="py-3 px-4 text-xs">
                            <div class="flex items-center gap-2">
                                <template x-if="user.phone">
                                    <div class="flex items-center gap-2">
                                        <a :href="`tel:${user.phone}`" 
                                           class="inline-flex items-center gap-1 text-slate-700 hover:text-orange-600 font-medium py-1 px-1.5 rounded hover:bg-slate-100 transition-colors"
                                           title="Call User">
                                            <span>📞</span>
                                            <span x-text="formatPhone(user.phone)"></span>
                                        </a>
                                        <a :href="getWhatsApp(user.phone)" 
                                           target="_blank" 
                                           class="w-7 h-7 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-600 flex items-center justify-center transition-colors"
                                           title="Chat on WhatsApp">
                                            <span>💬</span>
                                        </a>
                                    </div>
                                </template>
                                <template x-if="!user.phone">
                                    <span class="text-slate-400 italic">No phone set</span>
                                </template>
                            </div>
                        </td>

                        <!-- Workload (Leads & Tasks with clear tooltip) -->
                        <td class="py-3 px-4 text-center">
                            <div class="inline-flex items-center gap-1.5 text-xs">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded-md border border-orange-200/50" 
                                      :title="`Assigned Leads: ${user.leads_count}`">
                                    <span>👥</span>
                                    <span x-text="`${user.leads_count} Leads`"></span>
                                </span>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md border border-blue-200/50" 
                                      :title="`Assigned Tasks: ${user.tasks_count}`">
                                    <span>✓</span>
                                    <span x-text="`${user.tasks_count} Tasks`"></span>
                                </span>
                            </div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-3 px-4 text-center">
                            <template x-if="user.status === 'active'">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span>Active</span>
                                </span>
                            </template>
                            <template x-if="user.status !== 'active'">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-slate-100 text-slate-600 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    <span>Inactive</span>
                                </span>
                            </template>
                        </td>

                        <!-- Actions (View + More ⋮ menu) -->
                        <td class="py-3 px-4 text-right">
                            <div class="relative inline-flex items-center justify-end gap-1.5">
                                <!-- Quick View Button -->
                                <button type="button" 
                                        @click="openDetails(user)" 
                                        class="px-2.5 py-1 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition-colors cursor-pointer">
                                    View
                                </button>

                                <!-- More Menu Trigger -->
                                <button type="button" 
                                        @click="toggleMenu(user.id, $event)" 
                                        aria-label="More user actions"
                                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors cursor-pointer text-base font-bold">
                                    ⋮
                                </button>

                                <!-- Dropdown Menu Popover -->
                                <div x-show="activeMenuId === user.id" 
                                     @click.outside="closeMenu()"
                                     x-transition:enter="transition ease-out duration-100"
                                     x-transition:enter-start="transform opacity-0 scale-95"
                                     x-transition:enter-end="transform opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-75"
                                     x-transition:leave-start="transform opacity-100 scale-100"
                                     x-transition:leave-end="transform opacity-0 scale-95"
                                     class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-xl border border-slate-200/90 py-1.5 z-30 text-left"
                                     x-cloak>
                                    
                                    <button type="button" 
                                            @click="openDetails(user)" 
                                            class="w-full px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-orange-50 hover:text-orange-600 flex items-center gap-2 transition-colors cursor-pointer">
                                        <span>👁️</span>
                                        <span>View Profile & Workload</span>
                                    </button>

                                    <button type="button" 
                                            @click="openEdit(user, $event)" 
                                            class="w-full px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-orange-50 hover:text-orange-600 flex items-center gap-2 transition-colors cursor-pointer">
                                        <span>✏️</span>
                                        <span>Edit Details</span>
                                    </button>

                                    <button type="button" 
                                            @click="openPermissions(user, $event)" 
                                            class="w-full px-3.5 py-2 text-xs font-medium text-slate-700 hover:bg-purple-50 hover:text-purple-600 flex items-center gap-2 transition-colors cursor-pointer">
                                        <span>🛡️</span>
                                        <span>View Permissions</span>
                                    </button>

                                    <div class="my-1 border-t border-slate-100"></div>

                                    <!-- Deactivate / Activate quick toggle -->
                                    <template x-if="!user.is_you && user.email !== 'admin@sbl.test'">
                                        <button type="button" 
                                                @click="deactivateUser(user)" 
                                                class="w-full px-3.5 py-2 text-xs font-medium text-amber-700 hover:bg-amber-50 flex items-center gap-2 transition-colors cursor-pointer">
                                            <span>🔄</span>
                                            <span x-text="user.status === 'active' ? 'Deactivate Account' : 'Activate Account'"></span>
                                        </button>
                                    </template>

                                    <!-- Delete option -->
                                    <template x-if="!user.is_you && user.email !== 'admin@sbl.test'">
                                        <button type="button" 
                                                @click="promptDelete(user, $event)" 
                                                class="w-full px-3.5 py-2 text-xs font-medium text-rose-600 hover:bg-rose-50 flex items-center gap-2 transition-colors cursor-pointer">
                                            <span>🗑️</span>
                                            <span>Delete User</span>
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>

                <tr x-show="filteredUsers.length === 0" x-cloak>
                    <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                        <div class="max-w-xs mx-auto space-y-2">
                            <span class="text-3xl">🔍</span>
                            <div class="font-semibold text-slate-700">No users found</div>
                            <p class="text-xs text-slate-500">Try adjusting your search terms or filters.</p>
                            <button type="button" @click="resetFilters()" class="text-xs text-orange-600 font-semibold underline">Clear filters</button>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Mobile Responsive Cards View (<= 767px, md:hidden) -->
    <div class="md:hidden space-y-3">
        <template x-for="user in filteredUsers" :key="user.id">
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3 transition-all hover:border-slate-300">
                <!-- User Header -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-center gap-3 min-w-0 flex-1">
                        <div class="w-11 h-11 rounded-full bg-slate-900 text-orange-400 font-bold flex items-center justify-center text-sm shadow-xs border border-slate-700 flex-shrink-0">
                            <span x-text="getAvatarLetter(user.name)"></span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-slate-900 text-sm flex items-center gap-1.5 flex-wrap">
                                <span class="truncate" x-text="user.name"></span>
                                <template x-if="user.is_you">
                                    <span class="px-1.5 py-0.2 bg-orange-100 text-orange-800 text-[10px] rounded font-bold uppercase">You</span>
                                </template>
                            </div>
                            <div class="text-xs text-slate-500 font-medium truncate" x-text="user.designation"></div>
                            <div class="text-[11px] text-slate-400 font-mono" x-text="`📱 ${formatPhone(user.phone) || 'No phone'}`"></div>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold border flex-shrink-0"
                          :class="getRoleColor(user.role_slug)">
                        <span x-text="getRoleBgIcon(user.role_slug)"></span>
                        <span x-text="user.role_name"></span>
                    </span>
                </div>

                <!-- Workload & Status Strip -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-xs text-slate-600">
                    <!-- Workload Pill -->
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 bg-orange-50 text-orange-700 font-semibold rounded-md border border-orange-200/50"
                              x-text="`👥 ${user.leads_count} Leads`"></span>
                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 font-semibold rounded-md border border-blue-200/50"
                              x-text="`✓ ${user.tasks_count} Tasks`"></span>
                    </div>

                    <!-- Status -->
                    <div>
                        <template x-if="user.status === 'active'">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Active</span>
                            </span>
                        </template>
                        <template x-if="user.status !== 'active'">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-600">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>Inactive</span>
                            </span>
                        </template>
                    </div>
                </div>

                <!-- Action Touch Area (Minimum 44px buttons for mobile) -->
                <div class="pt-2 border-t border-slate-100 flex items-center justify-between gap-2">
                    <div class="flex items-center gap-1.5">
                        <template x-if="user.phone">
                            <a :href="`tel:${user.phone}`" 
                               class="min-h-[44px] min-w-[44px] px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <span>📞</span>
                                <span>Call</span>
                            </a>
                        </template>
                        <template x-if="user.phone">
                            <a :href="getWhatsApp(user.phone)" 
                               target="_blank" 
                               class="min-h-[44px] min-w-[44px] px-3 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold rounded-xl flex items-center justify-center gap-1.5 transition-colors">
                                <span>💬</span>
                                <span>WhatsApp</span>
                            </a>
                        </template>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" 
                                @click="openDetails(user)" 
                                class="min-h-[44px] px-3.5 bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-bold rounded-xl flex items-center justify-center transition-colors">
                            Details
                        </button>

                        <button type="button" 
                                @click="openEdit(user, $event)" 
                                class="min-h-[44px] px-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl flex items-center justify-center transition-colors">
                            Edit
                        </button>
                    </div>
                </div>
            </div>
        </template>

        <div x-show="filteredUsers.length === 0" class="p-8 text-center bg-white rounded-2xl border border-slate-200 text-slate-400 text-sm space-y-2" x-cloak>
            <span class="text-3xl">🔍</span>
            <div class="font-semibold text-slate-700">No users found</div>
            <p class="text-xs text-slate-500">No team members match your current filters.</p>
        </div>
    </div>

    <!-- USER DETAILS DRAWER (Slide-over on Desktop, Bottom Sheet on Mobile) -->
    <div role="dialog" aria-modal="true" tabindex="-1"
         x-show="detailsDrawerOpen" 
         class="fixed inset-0 z-50 overflow-hidden" 
         x-cloak>
        <!-- Backdrop -->
        <div x-show="detailsDrawerOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="closeDetails()" 
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10">
            <div x-show="detailsDrawerOpen" 
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-md bg-white shadow-2xl flex flex-col justify-between">
                
                <!-- Drawer Header -->
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">👤</span>
                        <h3 class="text-base font-bold text-slate-900">User Profile Details</h3>
                    </div>
                    <button type="button" @click="closeDetails()" class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-200/50 flex items-center justify-center text-lg font-bold cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Drawer Content -->
                <div class="flex-1 overflow-y-auto p-5 space-y-6" x-show="selectedUser">
                    <!-- Profile Card -->
                    <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-5 text-white shadow-md relative overflow-hidden">
                        <div class="absolute right-0 bottom-0 opacity-10 text-8xl font-black select-none pointer-events-none">SBL</div>
                        <div class="relative z-10 flex items-center gap-4">
                            <div class="w-14 h-14 rounded-2xl bg-orange-500 text-white font-extrabold text-2xl flex items-center justify-center shadow-lg border-2 border-white/20">
                                <span x-text="getAvatarLetter(selectedUser?.name)"></span>
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="text-lg font-bold truncate flex items-center gap-2">
                                    <span x-text="selectedUser?.name"></span>
                                    <template x-if="selectedUser?.is_you">
                                        <span class="px-2 py-0.5 bg-orange-500/80 text-white text-[10px] font-bold rounded uppercase">You</span>
                                    </template>
                                </div>
                                <div class="text-xs text-orange-300 font-medium" x-text="selectedUser?.designation"></div>
                                <div class="text-xs text-slate-400 mt-1" x-text="`Member Since: ${selectedUser?.created_at || 'N/A'}`"></div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-700/60 flex items-center justify-between text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-semibold bg-white/10 text-white border border-white/20">
                                <span x-text="getRoleBgIcon(selectedUser?.role_slug)"></span>
                                <span x-text="selectedUser?.role_name"></span>
                            </span>
                            <span class="font-medium" 
                                  :class="selectedUser?.status === 'active' ? 'text-emerald-400' : 'text-rose-400'"
                                  x-text="selectedUser?.status === 'active' ? '🟢 Active Account' : '🔴 Inactive Account'"></span>
                        </div>
                    </div>

                    <!-- Workload Breakdown -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Current Workload & Productivity</h4>
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-orange-50/70 border border-orange-200/80 rounded-xl p-3.5 space-y-1">
                                <div class="text-xs font-semibold text-orange-800 flex items-center gap-1">
                                    <span>👥</span>
                                    <span>Assigned Leads</span>
                                </div>
                                <div class="text-2xl font-black text-orange-900" x-text="selectedUser?.leads_count || 0"></div>
                                <div class="text-[11px] text-orange-700">Client CRM Pipeline</div>
                            </div>

                            <div class="bg-blue-50/70 border border-blue-200/80 rounded-xl p-3.5 space-y-1">
                                <div class="text-xs font-semibold text-blue-800 flex items-center gap-1">
                                    <span>✅</span>
                                    <span>Assigned Tasks</span>
                                </div>
                                <div class="text-2xl font-black text-blue-900" x-text="selectedUser?.tasks_count || 0"></div>
                                <div class="text-[11px] text-blue-700">Follow-ups & Actions</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact & Communication -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wider">Contact & Login Credentials</h4>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 space-y-3 text-xs">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Login Mobile:</span>
                                <span class="font-bold font-mono text-slate-800" x-text="formatPhone(selectedUser?.phone) || 'None'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">System Email:</span>
                                <span class="font-medium text-slate-700 truncate max-w-[220px]" x-text="selectedUser?.email || 'N/A'"></span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500">Account Role:</span>
                                <span class="font-semibold text-slate-800" x-text="selectedUser?.role_name"></span>
                            </div>
                        </div>

                        <!-- Direct Call / WhatsApp Links -->
                        <template x-if="selectedUser?.phone">
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <a :href="`tel:${selectedUser?.phone}`" 
                                   class="min-h-[44px] px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl font-semibold text-xs flex items-center justify-center gap-2 transition-colors">
                                    <span>📞 Call Direct</span>
                                </a>
                                <a :href="getWhatsApp(selectedUser?.phone)" 
                                   target="_blank" 
                                   class="min-h-[44px] px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold text-xs flex items-center justify-center gap-2 transition-colors shadow-xs">
                                    <span>💬 WhatsApp</span>
                                </a>
                            </div>
                        </template>
                    </div>

                    <!-- Permissions Quick Trigger -->
                    <div class="bg-purple-50/60 rounded-xl p-4 border border-purple-200/70 flex items-center justify-between">
                        <div>
                            <div class="font-bold text-xs text-purple-900">Role Permissions Matrix</div>
                            <div class="text-[11px] text-purple-700">View what modules this user can access</div>
                        </div>
                        <button type="button" 
                                @click="openPermissions(selectedUser)" 
                                class="px-3 py-1.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-semibold rounded-lg shadow-xs transition-colors cursor-pointer">
                            View Access
                        </button>
                    </div>
                </div>

                <!-- Drawer Footer -->
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-between gap-3">
                    <button type="button" 
                            @click="closeDetails()" 
                            class="min-h-[44px] px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl">
                        Close
                    </button>

                    <div class="flex items-center gap-2">
                        <template x-if="selectedUser">
                            <button type="button" 
                                    @click="openEdit(selectedUser)" 
                                    class="min-h-[44px] px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold rounded-xl shadow-xs transition-colors cursor-pointer">
                                Edit Profile
                            </button>
                        </template>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- ROLE & PERMISSIONS MATRIX MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1"
         x-show="permissionsModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="closePermissions()" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">🛡️</span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Effective Permissions: <span class="text-purple-700" x-text="permissionUser?.name"></span>
                        </h3>
                        <p class="text-xs text-slate-500">
                            Role: <span class="font-semibold text-slate-800" x-text="permissionUser?.role_name"></span>
                            <span class="mx-1">•</span>
                            <span x-text="permissionUser?.is_super_admin ? 'Full Super Admin Access' : 'Role-based Standard Access'"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="closePermissions()" class="text-slate-400 hover:text-slate-700 text-2xl font-bold">&times;</button>
            </div>

            <!-- Permission Modules Grid -->
            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-1">
                <template x-if="permissionUser?.is_super_admin">
                    <div class="p-4 bg-purple-50 rounded-xl border border-purple-200 text-purple-900 text-xs font-semibold flex items-center gap-3">
                        <span class="text-2xl">👑</span>
                        <div>
                            <div>Super Administrator Privileges</div>
                            <div class="text-[11px] font-normal text-purple-700 mt-0.5">This user holds complete administrative rights across all CRM, Presentation, Marketing, Reporting, and System settings.</div>
                        </div>
                    </div>
                </template>

                @php
                    $modules = [
                        'Leads' => ['Icon' => '👥', 'Desc' => 'Leads CRM, Pipeline & Conversion'],
                        'Tasks' => ['Icon' => '✅', 'Desc' => 'Tasks Management & Follow-ups'],
                        'Presentations' => ['Icon' => '📊', 'Desc' => 'Client Presentations & Demos'],
                        'Marketing' => ['Icon' => '📢', 'Desc' => 'Content Calendar & Marketing Resources'],
                        'Reports' => ['Icon' => '📈', 'Desc' => 'Business Analytics & Export'],
                        'Toolkit' => ['Icon' => '🧰', 'Desc' => 'SBL Packages, Commission & Counseling Tools'],
                        'Users' => ['Icon' => '👤', 'Desc' => 'Team & User Management'],
                        'Roles' => ['Icon' => '🛡️', 'Desc' => 'RBAC & Permission Configurations'],
                    ];
                @endphp

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    @foreach($modules as $moduleName => $meta)
                        <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-3.5 space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="font-bold text-xs text-slate-800 flex items-center gap-1.5">
                                    <span>{{ $meta['Icon'] }}</span>
                                    <span>{{ $moduleName }}</span>
                                </div>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                      :class="permissionUser?.is_super_admin || (roles.find(r => r.id === permissionUser?.role_id)?.permissions.some(p => p.module === '{{ $moduleName }}')) ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600'"
                                      x-text="permissionUser?.is_super_admin || (roles.find(r => r.id === permissionUser?.role_id)?.permissions.some(p => p.module === '{{ $moduleName }}')) ? 'Granted' : 'No Access'">
                                </span>
                            </div>
                            <div class="text-[11px] text-slate-500">{{ $meta['Desc'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100 flex items-center justify-end">
                <button type="button" @click="closePermissions()" class="min-h-[44px] px-5 py-2 text-xs font-semibold text-white bg-slate-800 hover:bg-slate-900 rounded-xl">
                    Close Matrix
                </button>
            </div>
        </div>
    </div>

    <!-- DELETE INTEGRITY / SAFETY WARNING MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1"
         x-show="deleteWarningModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="deleteWarningModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mx-auto">
                ⚠️
            </div>
            
            <div class="text-center space-y-1">
                <h3 class="text-base font-bold text-slate-900">User Has Associated Records</h3>
                <p class="text-xs text-slate-600">
                    ইউজার <span class="font-bold text-slate-800" x-text="userToDelete?.name"></span> এর সাথে 
                    <span class="font-bold text-orange-600" x-text="`${userToDelete?.leads_count || 0}টি লিড`"></span> এবং 
                    <span class="font-bold text-blue-600" x-text="`${userToDelete?.tasks_count || 0}টি টাস্ক`"></span> যুক্ত রয়েছে।
                </p>
            </div>

            <div class="bg-amber-50 rounded-xl p-3.5 border border-amber-200 text-xs text-amber-900 space-y-1">
                <div class="font-bold">সুপারিশকৃত নিরাপদ সমাধান:</div>
                <div class="text-[11px] text-amber-800 leading-relaxed">
                    সরাসরি ডিলিট করলে যুক্ত থাকা লিড ও কার্যক্রমের ডাটা বিচ্ছিন্ন (Orphaned) হয়ে যেতে পারে। এর বদলে অ্যাকাউন্টটি <strong>Inactive (নিষ্ক্রিয়)</strong> করে রাখা নিরাপদ।
                </div>
            </div>

            <div class="space-y-2 pt-2">
                <button type="button" 
                        @click="deactivateUser(userToDelete)" 
                        class="w-full min-h-[44px] px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer">
                    <span>🛑 Deactivate User (নিরাপদ বিকল্প)</span>
                </button>

                <button type="button" 
                        @click="submitDeleteForm(userToDelete.id, true)" 
                        class="w-full min-h-[44px] px-4 py-2.5 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs rounded-xl transition-colors flex items-center justify-center cursor-pointer">
                    <span>⚠️ জোরপূর্বক মুছে ফেলুন (Force Delete)</span>
                </button>

                <button type="button" 
                        @click="deleteWarningModalOpen = false" 
                        class="w-full min-h-[44px] px-4 py-2 text-xs font-semibold text-slate-500 hover:text-slate-700">
                    বাতিল (Cancel)
                </button>
            </div>
        </div>
    </div>

    <!-- ADD USER MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" 
         x-show="createModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="createModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🔐</span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Add New System User</h3>
                        <p class="text-[11px] text-slate-500">মোবাইল নম্বর ও পাসওয়ার্ড সেট করুন (লগইনের জন্য ব্যবহার হবে)</p>
                    </div>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" required placeholder="e.g. Mahfuzur Rahman" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mobile Number (লগইন আইডি) *</label>
                        <input type="tel" name="phone" required placeholder="01XXXXXXXXX" pattern="^(?:\+8801|8801|01)[3-9]\d{8}$" title="Valid Bangladesh mobile number (e.g. 017XXXXXXXX)" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-mono min-h-[44px]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Login Password *</label>
                        <input type="password" name="password" required minlength="6" placeholder="কমপক্ষে ৬ ক্যারেক্টার" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Assign Role *</label>
                        <select name="role_id" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Account Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                            <option value="active" selected>🟢 Active (লগইন করতে পারবে)</option>
                            <option value="inactive">🔴 Inactive (লগইন ব্লক থাকবে)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Designation (পদবী)</label>
                        <input type="text" name="designation" placeholder="e.g. Sales Executive" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email (ঐচ্ছিক / Optional)</label>
                        <input type="email" name="email" placeholder="ফাঁকা রাখলে অটো সেট হবে" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="min-h-[44px] px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="min-h-[44px] px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors">Create User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT USER MODAL -->
    <div role="dialog" aria-modal="true" tabindex="-1" 
         x-show="editModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="editModalOpen = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Edit User: <span class="text-orange-600" x-text="editingUser.name"></span></h3>
                        <p class="text-[11px] text-slate-500">ইউজার তথ্য, মোবাইল নম্বর বা পাসওয়ার্ড পরিবর্তন</p>
                    </div>
                </div>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold cursor-pointer">&times;</button>
            </div>

            <form :action="'{{ url('/users') }}/' + editingUser.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                    <input type="text" name="name" x-model="editingUser.name" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mobile Number (লগইন আইডি) *</label>
                        <input type="tel" name="phone" x-model="editingUser.phone" required pattern="^(?:\+8801|8801|01)[3-9]\d{8}$" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-mono min-h-[44px]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Set New Password</label>
                        <input type="password" name="password" minlength="6" placeholder="পরিবর্তন না চাইলে ফাঁকা রাখুন" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Account Status</label>
                        <select name="status" x-model="editingUser.status" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none font-semibold min-h-[44px]">
                            <option value="active">🟢 Active (লগইন অনুমোদিত)</option>
                            <option value="inactive" :disabled="editingUser.id === currentUserId">🔴 Inactive (লগইন নিষ্ক্রিয়)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role *</label>
                        <select name="role_id" x-model="editingUser.role_id" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Designation</label>
                        <input type="text" name="designation" x-model="editingUser.designation" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email Address</label>
                        <input type="email" name="email" x-model="editingUser.email" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none min-h-[44px]">
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="min-h-[44px] px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="min-h-[44px] px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors">Update User</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
