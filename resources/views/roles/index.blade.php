@extends('layouts.app')

@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'Control what each role can view, create, edit and manage.')

@section('content')
@php
    $rolesData = $roles->map(function ($r) {
        return [
            'id' => $r->id,
            'name' => $r->name,
            'slug' => $r->slug,
            'description' => $r->description,
            'is_system' => (bool) $r->is_system,
            'users_count' => (int) $r->users_count,
            'permissions_count' => (int) $r->permissions_count,
            'permissions' => $r->permissions->map(function ($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'slug' => $p->slug,
                    'module' => $p->module,
                    'description' => $p->description,
                ];
            })->values(),
        ];
    });

    $permissionsData = $permissions->map(function ($p) {
        return [
            'id' => $p->id,
            'name' => $p->name,
            'slug' => $p->slug,
            'module' => $p->module,
            'description' => $p->description,
            'roles' => $p->roles->map(function ($r) {
                return [
                    'id' => $r->id,
                    'name' => $r->name,
                    'slug' => $r->slug,
                    'is_system' => (bool) $r->is_system,
                ];
            })->values(),
        ];
    });

    $isSuperAdmin = auth()->user() && auth()->user()->isSuperAdmin();
@endphp

<div class="space-y-6" 
     x-data="roleManagement({
         roles: {{ Js::from($rolesData) }},
         permissions: {{ Js::from($permissionsData) }},
         allPermissionsCount: {{ $allPermissionsCount }},
         isSuperAdmin: {{ $isSuperAdmin ? 'true' : 'false' }}
     })"
     x-cloak>

    <!-- Page Header with Subtitle and Primary Action -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-4 border-b border-slate-200/80">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                <span>🛡️</span>
                <span x-text="$store.lang && $store.lang.current === 'bn' ? 'রোল ও পারমিশনস' : 'Roles & Permissions'">Roles & Permissions</span>
            </h1>
            <p class="text-xs sm:text-sm text-slate-500 mt-1">Control what each role can view, create, edit and manage.</p>
        </div>

        <!-- Right Header Action: Create Role -->
        <div class="flex items-center gap-2">
            @can('roles.manage')
            <button type="button" 
                    @click="openCreateModal()" 
                    class="btn-primary w-full sm:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl text-xs sm:text-sm font-semibold shadow-xs">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                <span x-text="$store.lang && $store.lang.current === 'bn' ? 'নতুন রোল তৈরি করুন' : 'Create Role'">Create Role</span>
            </button>
            @endcan
        </div>
    </div>

    <!-- Navigation View Tabs -->
    <div class="flex items-center justify-between gap-4">
        <nav aria-label="Roles and Permissions Sections" class="inline-flex p-1 bg-slate-100/90 rounded-2xl border border-slate-200/80 shadow-xs">
            <button type="button" 
                    @click="setTab('roles')" 
                    :class="activeTab === 'roles' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'" 
                    class="px-4 py-2 text-xs sm:text-sm rounded-xl transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg>
                <span x-text="$store.lang && $store.lang.current === 'bn' ? 'রোলসমূহ (' + roles.length + ')' : 'Roles (' + roles.length + ')'">Roles</span>
            </button>
            <button type="button" 
                    @click="setTab('permissions')" 
                    :class="activeTab === 'permissions' ? 'bg-white text-slate-900 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900 font-semibold'" 
                    class="px-4 py-2 text-xs sm:text-sm rounded-xl transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                <span x-text="$store.lang && $store.lang.current === 'bn' ? 'পারমিশন ক্যাটালগ (' + allPermissionsCount + ')' : 'Permissions Catalog (' + allPermissionsCount + ')'">Permissions Catalog</span>
            </button>
        </nav>
    </div>

    <!-- ========================================== -->
    <!-- TAB 1: ROLES MANAGEMENT VIEW               -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'roles'" class="space-y-5">
        
        <!-- Filter Bar -->
        <div class="flex flex-wrap items-center justify-between gap-3 bg-white p-3 sm:p-4 rounded-2xl border border-slate-200/80 shadow-xs">
            <!-- Filter Chips -->
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider mr-1 hidden sm:inline" x-text="$store.lang && $store.lang.current === 'bn' ? 'ফিল্টার:' : 'Filter:'">Filter:</span>
                
                <button type="button" 
                        @click="roleFilter = 'all'" 
                        :class="roleFilter === 'all' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'" 
                        class="px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5">
                    <span x-text="$store.lang && $store.lang.current === 'bn' ? 'সকল রোল' : 'All Roles'">All Roles</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="roleFilter === 'all' ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-600'" x-text="roles.length"></span>
                </button>

                <button type="button" 
                        @click="roleFilter = 'system'" 
                        :class="roleFilter === 'system' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'" 
                        class="px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5">
                    <span class="text-xs">🔒</span>
                    <span x-text="$store.lang && $store.lang.current === 'bn' ? 'সিস্টেম রোল' : 'System Roles'">System Roles</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="roleFilter === 'system' ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-600'" x-text="systemRolesCount"></span>
                </button>

                <button type="button" 
                        @click="roleFilter = 'custom'" 
                        :class="roleFilter === 'custom' ? 'bg-slate-900 text-white font-bold' : 'bg-slate-100 text-slate-700 hover:bg-slate-200 font-semibold'" 
                        class="px-3 py-1.5 rounded-xl text-xs transition-colors flex items-center gap-1.5">
                    <span class="text-xs">✨</span>
                    <span x-text="$store.lang && $store.lang.current === 'bn' ? 'কাস্টম রোল' : 'Custom Roles'">Custom Roles</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px]" :class="roleFilter === 'custom' ? 'bg-slate-700 text-slate-200' : 'bg-slate-200 text-slate-600'" x-text="customRolesCount"></span>
                </button>
            </div>

            <!-- Total Users Shortcut Link -->
            <div class="text-xs text-slate-500 font-medium">
                <a href="{{ route('users.index') }}" class="text-orange-600 hover:text-orange-700 font-semibold inline-flex items-center gap-1">
                    <span x-text="$store.lang && $store.lang.current === 'bn' ? 'ইউজার ম্যানেজমেন্ট দেখুন' : 'Go to User Management'">Go to User Management</span> &rarr;
                </a>
            </div>
        </div>

        <!-- Roles Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 sm:gap-6">
            <template x-for="role in filteredRoles" :key="role.id">
                <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs hover:shadow-md transition-all flex flex-col justify-between p-5 space-y-4">
                    
                    <!-- Card Top: Name, Badge, Menu -->
                    <div class="space-y-3">
                        <div class="flex items-start justify-between gap-2">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h3 class="font-bold text-slate-900 text-base leading-snug truncate" x-text="role.name"></h3>
                                    
                                    <!-- Badge Logic -->
                                    <template x-if="role.slug === 'super-admin'">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">
                                            <span>🔒</span>
                                            <span>SYSTEM PROTECTED</span>
                                        </span>
                                    </template>
                                    <template x-if="role.slug !== 'super-admin' && role.is_system">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-200">
                                            SYSTEM ROLE
                                        </span>
                                    </template>
                                    <template x-if="!role.is_system">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            CUSTOM ROLE
                                        </span>
                                    </template>
                                </div>
                                
                                <div class="text-[11px] font-mono text-slate-400 mt-0.5" x-text="role.slug"></div>
                            </div>

                            <!-- Actions Dropdown -->
                            <div class="relative" x-data="{ menuOpen: false }">
                                <button type="button" 
                                        @click="menuOpen = !menuOpen" 
                                        @click.away="menuOpen = false"
                                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" 
                                        title="Role Actions">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" /></svg>
                                </button>
                                
                                <div x-show="menuOpen" 
                                     x-transition 
                                     x-cloak 
                                     class="absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-slate-200 py-1 z-20 text-xs">
                                    <button type="button" 
                                            @click="menuOpen = false; openEditInfo(role)" 
                                            class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-50 font-medium flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                        <span x-text="$store.lang && $store.lang.current === 'bn' ? 'রোল তথ্য সম্পাদনা' : 'Edit Role Info'">Edit Role Info</span>
                                    </button>

                                    @can('roles.manage')
                                    <button type="button" 
                                            @click="menuOpen = false; duplicateRole(role)" 
                                            class="w-full text-left px-3 py-2 text-slate-700 hover:bg-slate-50 font-medium flex items-center gap-2">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" /></svg>
                                        <span x-text="$store.lang && $store.lang.current === 'bn' ? 'ডুপ্লিকেট রোল' : 'Duplicate Role'">Duplicate Role</span>
                                    </button>
                                    @endcan

                                    <template x-if="!role.is_system">
                                        <div>
                                            <div class="border-t border-slate-100 my-1"></div>
                                            <button type="button" 
                                                    @click="menuOpen = false; promptDeleteRole(role)" 
                                                    class="w-full text-left px-3 py-2 text-rose-600 hover:bg-rose-50 font-medium flex items-center gap-2">
                                                <svg class="w-3.5 h-3.5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                <span x-text="$store.lang && $store.lang.current === 'bn' ? 'রোল ডিলিট করুন' : 'Delete Role'">Delete Role</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <p class="text-xs text-slate-600 leading-relaxed line-clamp-2 min-h-[36px]" 
                           x-text="role.description || (role.is_system ? 'System predefined operational role.' : 'Custom configured access role.')"></p>

                        <!-- Key Access Modules -->
                        <div class="space-y-1">
                            <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider" x-text="$store.lang && $store.lang.current === 'bn' ? 'মূল অ্যাক্সেস মডিউলসমূহ:' : 'Key Access: '">Key Access:</div>
                            <div class="text-xs font-semibold text-slate-700 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-100 flex items-center gap-1.5 truncate">
                                <span>🔐</span>
                                <span class="truncate" x-text="getRoleKeyModules(role)"></span>
                            </div>
                        </div>

                        <!-- Stats & Coverage Bar -->
                        <div class="space-y-2 pt-2">
                            <div class="flex items-center justify-between text-xs">
                                <!-- Users count linking directly to filtered users list -->
                                <a :href="'/users?role=' + role.slug" 
                                   class="font-semibold text-slate-600 hover:text-orange-600 transition-colors flex items-center gap-1.5">
                                    <span>👥</span>
                                    <span x-text="role.users_count + (role.users_count === 1 ? ' User' : ' Users')"></span>
                                </a>

                                <!-- Permission coverage ratio -->
                                <div class="font-semibold text-indigo-700 flex items-center gap-1">
                                    <span x-text="role.permissions_count + ' / ' + allPermissionsCount"></span>
                                    <span class="text-[10px] text-slate-400 font-normal" x-text="'(' + getCoveragePercent(role) + '%)'"></span>
                                </div>
                            </div>

                            <!-- Neutral Coverage Bar -->
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="h-1.5 rounded-full transition-all duration-300" 
                                     :class="role.slug === 'super-admin' ? 'bg-purple-600' : 'bg-indigo-600'" 
                                     :style="'width: ' + getCoveragePercent(role) + '%'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Card Buttons -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <!-- Primary action: Manage / View Permissions -->
                        <button type="button" 
                                @click="openPermEditor(role)" 
                                :class="role.slug === 'super-admin' ? 'bg-purple-50 hover:bg-purple-100 text-purple-700 border-purple-200' : 'bg-orange-50 hover:bg-orange-100 text-orange-700 border-orange-200'" 
                                class="px-3.5 py-2 text-xs font-bold rounded-xl border transition-colors flex items-center gap-1.5 flex-1 justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                            <span x-text="role.slug === 'super-admin' ? ($store.lang && $store.lang.current === 'bn' ? 'পারমিশন দেখুন' : 'View Permissions') : ($store.lang && $store.lang.current === 'bn' ? 'পারমিশন কনফিগার' : 'Manage Permissions')"></span>
                        </button>

                        <!-- Secondary action: View Users -->
                        <a :href="'/users?role=' + role.slug" 
                           class="px-3 py-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-xl transition-colors flex items-center gap-1">
                            <span x-text="$store.lang && $store.lang.current === 'bn' ? 'ইউজার্স' : 'View Users'">View Users</span>
                        </a>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty Filter State -->
        <div x-show="filteredRoles.length === 0" class="bg-white rounded-2xl border border-slate-200 p-8 text-center space-y-3">
            <div class="text-3xl">🔍</div>
            <h4 class="font-bold text-slate-800 text-sm" x-text="$store.lang && $store.lang.current === 'bn' ? 'কোনো রোল পাওয়া যায়নি' : 'No roles found in this filter'">No roles found</h4>
            <p class="text-xs text-slate-500" x-text="$store.lang && $store.lang.current === 'bn' ? 'ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।' : 'Try changing your filter selection above.'"></p>
            <button type="button" @click="roleFilter = 'all'" class="px-3.5 py-1.5 text-xs font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors">Show All Roles</button>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 2: PERMISSIONS AUDIT CATALOG           -->
    <!-- ========================================== -->
    <div x-show="activeTab === 'permissions'" class="space-y-4">
        <!-- Catalog Controls -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="w-full sm:w-72 relative">
                <input type="text" 
                       x-model="catalogSearch" 
                       placeholder="Search permissions by name, key, or module..." 
                       class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
            </div>

            <div class="w-full sm:w-auto flex items-center gap-2">
                <label class="text-xs font-bold text-slate-500 whitespace-nowrap">Module:</label>
                <select x-model="catalogModule" class="w-full sm:w-auto px-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    <option value="all">All Modules</option>
                    <template x-for="mod in modulesList" :key="mod">
                        <option :value="mod" x-text="mod"></option>
                    </template>
                </select>
            </div>
        </div>

        <!-- Catalog Table -->
        <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 uppercase tracking-wider text-[10px] font-bold">
                            <th class="py-3 px-4">Permission Name & Key</th>
                            <th class="py-3 px-4">Module</th>
                            <th class="py-3 px-4">Description</th>
                            <th class="py-3 px-4">Assigned Roles</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="perm in filteredCatalogPermissions" :key="perm.id">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 flex items-center gap-1.5">
                                        <span x-text="perm.name"></span>
                                        <template x-if="isPermDangerous(perm)">
                                            <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-50 text-amber-700 border border-amber-200" title="Sensitive operational permission">
                                                ⚠️ Sensitive
                                            </span>
                                        </template>
                                    </div>
                                    <div class="font-mono text-[10px] text-slate-400 mt-0.5" x-text="perm.slug"></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md font-semibold text-[11px] bg-indigo-50 text-indigo-700 border border-indigo-100" x-text="perm.module"></span>
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 max-w-xs truncate" x-text="perm.description || 'System access permission.'"></td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-sm">
                                        <template x-for="r in perm.roles" :key="r.id">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium" 
                                                  :class="r.slug === 'super-admin' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-700 border border-slate-200'" 
                                                  x-text="r.name"></span>
                                        </template>
                                        <template x-if="!perm.roles || perm.roles.length === 0">
                                            <span class="text-slate-400 italic text-[11px]">Unassigned</span>
                                        </template>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Empty Table State -->
            <div x-show="filteredCatalogPermissions.length === 0" class="p-8 text-center text-slate-500 text-xs">
                No permissions matching your criteria.
            </div>
        </div>
    </div>

    <!-- ======================================================== -->
    <!-- PERMISSIONS SLIDE-OVER DRAWER (Desktop & Full-Screen Mobile)-->
    <!-- ======================================================== -->
    <div x-show="permEditorOpen" 
         x-cloak
         class="fixed inset-0 z-50 overflow-hidden" 
         role="dialog" 
         aria-modal="true">
        
        <!-- Backdrop -->
        <div x-show="permEditorOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="requestClosePermEditor()"
             class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs"></div>

        <!-- Drawer Panel -->
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-0 sm:pl-10">
            <div x-show="permEditorOpen" 
                 x-transition:enter="transform transition ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="w-screen max-w-2xl bg-white shadow-2xl flex flex-col h-full">
                
                <template x-if="activeRole">
                    <form :action="'/roles/' + activeRole.id" method="POST" class="flex flex-col h-full">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="name" :value="activeRole.name">
                        <input type="hidden" name="description" :value="activeRole.description || ''">

                        <!-- Drawer Header -->
                        <div class="p-5 sm:p-6 border-b border-slate-200 bg-slate-50/80 flex items-start justify-between gap-4">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-lg font-bold text-slate-900" x-text="activeRole.slug === 'super-admin' ? 'View Permissions: ' + activeRole.name : 'Manage Permissions: ' + activeRole.name"></h2>
                                    <template x-if="activeRole.slug === 'super-admin'">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800 uppercase">System Protected</span>
                                    </template>
                                </div>
                                <p class="text-xs text-slate-500" x-text="$store.lang && $store.lang.current === 'bn' ? 'এই রোলের জন্য অনুমোদিত পারমিশনসমূহ নিয়ন্ত্রণ করুন।' : 'Configure granted capabilities and operational authorizations for this role.'"></p>
                                
                                <div class="flex items-center gap-3 pt-2 text-xs font-semibold">
                                    <span class="text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100" 
                                          x-text="checkedPermIds.length + ' / ' + allPermissionsCount + ' Permissions Enabled'"></span>
                                    <span class="text-slate-400 font-mono text-[11px]" x-text="'Slug: ' + activeRole.slug"></span>
                                </div>
                            </div>

                            <button type="button" 
                                    @click="requestClosePermEditor()" 
                                    class="p-2 text-slate-400 hover:text-slate-700 rounded-xl hover:bg-slate-200/60 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Super Admin Safety Notice -->
                        <template x-if="activeRole.slug === 'super-admin'">
                            <div class="bg-purple-50 border-b border-purple-100 px-5 py-3 text-xs text-purple-900 flex items-start gap-2.5">
                                <span class="text-base">🔒</span>
                                <div class="leading-relaxed">
                                    <strong>Full System Access:</strong> Super Admin retains unconditional administrative authorization across all system modules. Individual toggles are locked to protect against accidental administrative lockout.
                                </div>
                            </div>
                        </template>

                        <!-- Drawer Search & Quick Controls -->
                        <div class="p-4 border-b border-slate-100 bg-white flex flex-col sm:flex-row items-center justify-between gap-3">
                            <div class="w-full relative">
                                <input type="text" 
                                       x-model="permSearch" 
                                       placeholder="Filter permissions in this role..." 
                                       class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                            </div>

                            <template x-if="activeRole.slug !== 'super-admin'">
                                <div class="flex items-center gap-2 text-xs w-full sm:w-auto justify-end">
                                    <button type="button" 
                                            @click="checkedPermIds = permissions.map(p => Number(p.id))" 
                                            class="px-2.5 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-semibold rounded-lg transition-colors whitespace-nowrap">
                                        Grant All
                                    </button>
                                    <button type="button" 
                                            @click="checkedPermIds = []" 
                                            class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg transition-colors whitespace-nowrap">
                                        Revoke All
                                    </button>
                                </div>
                            </template>
                        </div>

                        <!-- Permissions Modules Accordion / List -->
                        <div class="p-5 overflow-y-auto flex-1 space-y-4">
                            <template x-for="(perms, moduleName) in groupedModules" :key="moduleName">
                                <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden shadow-2xs">
                                    <!-- Module Header -->
                                    <div class="p-3.5 bg-slate-50/90 border-b border-slate-100 flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 text-sm" x-text="moduleName"></span>
                                            <span class="text-[11px] font-semibold text-slate-400" 
                                                  x-text="'(' + getModuleCheckedCount(moduleName) + ' / ' + getModuleTotalCount(moduleName) + ' enabled)'"></span>
                                        </div>

                                        <template x-if="activeRole.slug !== 'super-admin'">
                                            <div class="flex items-center gap-2 text-xs">
                                                <button type="button" 
                                                        @click="toggleModule(moduleName, true)" 
                                                        class="text-orange-600 hover:text-orange-800 font-semibold px-1">All</button>
                                                <span class="text-slate-300">|</span>
                                                <button type="button" 
                                                        @click="toggleModule(moduleName, false)" 
                                                        class="text-slate-400 hover:text-slate-600 font-semibold px-1">None</button>
                                            </div>
                                        </template>
                                    </div>

                                    <!-- Permissions Items in Module -->
                                    <div class="p-3 divide-y divide-slate-100 space-y-1">
                                        <template x-for="perm in perms" :key="perm.id">
                                            <label class="flex items-start gap-3 p-2 rounded-xl hover:bg-slate-50 transition-colors cursor-pointer select-none">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       :value="perm.id" 
                                                       :checked="isPermChecked(perm.id)"
                                                       @change="togglePerm(perm.id)"
                                                       :disabled="activeRole.slug === 'super-admin'"
                                                       class="mt-1 w-4 h-4 text-orange-600 rounded border-slate-300 focus:ring-orange-500 disabled:opacity-50">
                                                
                                                <div class="text-xs flex-1 min-w-0">
                                                    <div class="font-semibold text-slate-800 flex items-center gap-2 flex-wrap">
                                                        <span x-text="perm.name"></span>
                                                        <span class="text-[10px] font-mono text-slate-400" x-text="'(' + perm.slug + ')'"></span>
                                                        <template x-if="isPermDangerous(perm)">
                                                            <span class="text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded px-1">⚠️ Sensitive</span>
                                                        </template>
                                                    </div>
                                                    <div class="text-[11px] text-slate-500 mt-0.5 leading-snug" x-text="perm.description || 'Access authorization for this action.'"></div>
                                                </div>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Sticky Drawer Footer -->
                        <div class="p-4 border-t border-slate-200 bg-white flex items-center justify-between gap-3">
                            <div class="text-xs text-slate-500">
                                <template x-if="isDirty && activeRole.slug !== 'super-admin'">
                                    <span class="text-amber-600 font-semibold flex items-center gap-1">
                                        <span>⚠️</span>
                                        <span>Unsaved changes</span>
                                    </span>
                                </template>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" 
                                        @click="requestClosePermEditor()" 
                                        class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800 rounded-xl transition-colors">
                                    <span x-text="activeRole.slug === 'super-admin' ? 'Close' : 'Cancel'">Cancel</span>
                                </button>

                                <template x-if="activeRole.slug !== 'super-admin'">
                                    <button type="submit" 
                                            class="px-5 py-2 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        <span x-text="$store.lang && $store.lang.current === 'bn' ? 'পরিবর্তন সংরক্ষণ করুন' : 'Save Changes'">Save Changes</span>
                                    </button>
                                </template>
                            </div>
                        </div>
                    </form>
                </template>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- CREATE ROLE MODAL                          -->
    <!-- ========================================== -->
    <div x-show="createModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         role="dialog" 
         aria-modal="true">
        <div @click.away="createModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛡️</span>
                    <h3 class="text-base font-bold text-slate-900" x-text="$store.lang && $store.lang.current === 'bn' ? 'কাস্টম রোল তৈরি করুন' : 'Create Custom Role'">Create Custom Role</h3>
                </div>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role Title *</label>
                    <input type="text" 
                           name="name" 
                           x-model="newRole.name" 
                           required 
                           placeholder="e.g. Regional Support Officer" 
                           class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                    <textarea name="description" 
                              x-model="newRole.description" 
                              rows="2" 
                              placeholder="Brief description of duties and responsibilities..." 
                              class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Copy Permissions From (Optional)</label>
                    <select name="copy_role_id" 
                            x-model="newRole.copy_role_id" 
                            class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                        <option value="">Start from scratch (0 permissions)</option>
                        <template x-for="r in roles" :key="r.id">
                            <option :value="r.id" x-text="r.name + ' (' + r.permissions_count + ' permissions)'"></option>
                        </template>
                    </select>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors">Create Role</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- EDIT ROLE INFO MODAL                       -->
    <!-- ========================================== -->
    <div x-show="editInfoModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4" 
         role="dialog" 
         aria-modal="true">
        <div @click.away="editInfoModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">✏️</span>
                    <h3 class="text-base font-bold text-slate-900" x-text="$store.lang && $store.lang.current === 'bn' ? 'রোল তথ্য সম্পাদনা' : 'Edit Role Information'">Edit Role Info</h3>
                </div>
                <button type="button" @click="editInfoModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <template x-if="editingInfoRole.id">
                <form :action="'/roles/' + editingInfoRole.id" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role Title *</label>
                        <input type="text" 
                               name="name" 
                               x-model="editingInfoRole.name" 
                               required 
                               class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Identifier Slug</label>
                        <input type="text" 
                               :value="editingInfoRole.slug" 
                               disabled 
                               class="w-full px-3 py-2 text-sm bg-slate-100 text-slate-500 font-mono border border-slate-200 rounded-xl cursor-not-allowed">
                        <p class="text-[11px] text-slate-400 mt-1">Slugs are system identifiers and cannot be renamed directly.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                        <textarea name="description" 
                                  x-model="editingInfoRole.description" 
                                  rows="2" 
                                  class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" @click="editInfoModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors">Save Details</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- UNSAVED CHANGES WARNING MODAL              -->
    <!-- ========================================== -->
    <div x-show="unsavedChangesOpen" 
         x-cloak 
         class="fixed inset-0 z-60 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-sm w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <div class="flex items-center gap-3 text-amber-600">
                <span class="text-2xl">⚠️</span>
                <h4 class="font-bold text-slate-900 text-base" x-text="$store.lang && $store.lang.current === 'bn' ? 'অসংরক্ষিত পরিবর্তন রয়েছে!' : 'Discard unsaved changes?'">Discard changes?</h4>
            </div>
            <p class="text-xs text-slate-600 leading-relaxed" x-text="$store.lang && $store.lang.current === 'bn' ? 'আপনার করা পারমিশন পরিবর্তনগুলো এখনও সংরক্ষণ করা হয়নি। আপনি কি নিশ্চিত যে পরিবর্তনগুলো বাতিল করে বের হতে চান?' : 'You have unsaved changes to role permissions. Are you sure you want to discard them?'"></p>
            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" 
                        @click="unsavedChangesOpen = false" 
                        class="px-3.5 py-1.5 text-xs font-semibold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl">Keep Editing</button>
                <button type="button" 
                        @click="confirmDiscardChanges()" 
                        class="px-3.5 py-1.5 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">Discard & Exit</button>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- DELETE ROLE CONFIRMATION MODAL             -->
    <!-- ========================================== -->
    <div x-show="deleteModalOpen" 
         x-cloak 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4">
            <template x-if="deleteWarning">
                <div class="space-y-4">
                    <div class="flex items-center gap-2 text-amber-600">
                        <span class="text-2xl">⚠️</span>
                        <h4 class="font-bold text-slate-900 text-base">Cannot Delete Role</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed" x-text="deleteWarning"></p>
                    <div class="pt-2 flex items-center justify-end gap-2">
                        <a href="{{ route('users.index') }}" class="px-4 py-2 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl">Manage Users &rarr;</a>
                        <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Close</button>
                    </div>
                </div>
            </template>

            <template x-if="!deleteWarning && roleToDelete">
                <form :action="'/roles/' + roleToDelete.id" method="POST" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div class="flex items-center gap-2 text-rose-600">
                        <span class="text-2xl">🗑️</span>
                        <h4 class="font-bold text-slate-900 text-base" x-text="'Delete Role: ' + roleToDelete.name"></h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Are you sure you want to permanently delete custom role <strong class="text-slate-900" x-text="roleToDelete.name"></strong>? This action cannot be undone.
                    </p>
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button type="button" @click="deleteModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                        <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-rose-600 hover:bg-rose-700 rounded-xl">Confirm Delete</button>
                    </div>
                </form>
            </template>
        </div>
    </div>

</div>
@endsection
