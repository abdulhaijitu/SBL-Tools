@extends('layouts.app')

@section('page-title', 'Roles & Permissions')
@section('page-subtitle', 'Control granular access levels, authorization rules, and administrative roles')

@section('content')
<div class="space-y-6" x-data="{ createModalOpen: false }">

    <!-- Top Banner & Security Summary -->
    <div class="bg-gradient-to-r from-slate-950 via-slate-900 to-indigo-950 text-white p-6 rounded-2xl border border-slate-800 shadow-md flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 rounded-full text-xs font-bold uppercase tracking-wider">
                <span>🛡️</span> Role-Based Access Control (RBAC)
            </div>
            <h2 class="text-xl md:text-2xl font-bold tracking-tight">Security & Permission System</h2>
            <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                Configure what each team member can view, create, edit, or delete across Leads, Tasks, Presentations, Marketing, and Financial Reports.
            </p>
        </div>

        <button @click="createModalOpen = true" class="px-5 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Create Custom Role</span>
        </button>
    </div>

    <!-- Roles Grid Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col justify-between hover:shadow-md transition-shadow">
            <div class="space-y-4">
                
                <!-- Role Header & Badge -->
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                            <span>{{ $role->name }}</span>
                        </h3>
                        <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $role->slug }}</div>
                    </div>

                    @if($role->is_system)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-50 text-purple-700 border border-purple-200">
                            System Role
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-slate-100 text-slate-700 border border-slate-200">
                            Custom Role
                        </span>
                    @endif
                </div>

                <!-- Description -->
                <p class="text-xs text-slate-600 leading-relaxed min-h-[36px]">
                    {{ $role->description ?: 'No description specified for this role.' }}
                </p>

                <!-- Stats Bar -->
                <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-center justify-between text-xs font-semibold">
                    <div class="text-slate-600">
                        👥 <span>{{ $role->users_count }}</span> Member(s)
                    </div>
                    <div class="text-indigo-600">
                        🔐 <span>{{ $role->permissions_count }} / {{ $allPermissionsCount }}</span> Active
                    </div>
                </div>

                <!-- Permissions Preview Chips -->
                <div class="space-y-1.5">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Assigned Capabilities:</div>
                    <div class="flex flex-wrap gap-1.5 max-h-[72px] overflow-hidden">
                        @forelse($role->permissions->take(5) as $perm)
                            <span class="px-2 py-0.5 bg-slate-100 text-slate-700 rounded text-[11px]">
                                {{ $perm->name }}
                            </span>
                        @empty
                            <span class="text-xs text-slate-400 italic">No permissions assigned yet.</span>
                        @endforelse

                        @if($role->permissions->count() > 5)
                            <span class="px-2 py-0.5 bg-indigo-50 text-indigo-700 font-semibold rounded text-[11px]">
                                +{{ $role->permissions->count() - 5 }} more
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Card Bottom Actions -->
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('roles.edit', $role) }}" class="px-3.5 py-1.5 bg-orange-50 hover:bg-orange-100 text-orange-700 text-xs font-semibold rounded-xl transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Edit Permissions</span>
                </a>

                @if(!$role->is_system)
                    <form action="{{ route('roles.destroy', $role) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete role {{ addslashes($role->name) }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-slate-400 hover:text-rose-600 font-semibold text-xs px-2 py-1 transition-colors">
                            Delete Role
                        </button>
                    </form>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    <!-- CREATE ROLE MODAL -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4"
         x-transition
         x-cloak>
        <div @click.away="createModalOpen = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-5">
            
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="text-xl">🛡️</span>
                    <h3 class="text-base font-bold text-slate-900">Create Custom Role</h3>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold">&times;</button>
            </div>

            <form action="{{ route('roles.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role Title *</label>
                    <input type="text" name="name" required placeholder="e.g. Operations Coordinator" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role Description</label>
                    <textarea name="description" rows="3" placeholder="Briefly describe what responsibilities this role entails..." class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none"></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-sm transition-colors">Continue to Permissions &rarr;</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
