@extends('layouts.app')

@section('page-title', 'Configure Role: ' . $role->name)
@section('page-subtitle', 'Define granted permissions and operational privileges for this role')

@section('content')
<div class="space-y-6" x-data="{
    toggleModule(moduleClass, checked) {
        document.querySelectorAll('.' + moduleClass).forEach(cb => cb.checked = checked);
    },
    selectAll(state) {
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = state);
    }
}">

    <!-- Back Navigation & Header Bar -->
    <div class="flex items-center justify-between gap-4">
        <a href="{{ route('roles.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-600 hover:text-slate-900 bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-xs transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            <span>Back to Roles</span>
        </a>

        <div class="flex items-center gap-2">
            <button type="button" @click="selectAll(true)" class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold rounded-xl transition-colors">
                Grant All Permissions
            </button>
            <button type="button" @click="selectAll(false)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-xl transition-colors">
                Revoke All
            </button>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('roles.update', $role) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- Role Metadata Card -->
        <div class="bg-white rounded-2xl p-5 md:p-6 border border-slate-200/80 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="font-bold text-slate-900 text-base flex items-center gap-2">
                    <span>🛡️</span> Role Information
                </h3>
                @if($role->is_system)
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase bg-purple-50 text-purple-700 border border-purple-200">
                        System Protected
                    </span>
                @endif
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Role Title *</label>
                    <input type="text" name="name" value="{{ old('name', $role->name) }}" required class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Slug Identifier</label>
                    <input type="text" value="{{ $role->slug }}" disabled class="w-full px-3 py-2 text-sm bg-slate-100 text-slate-500 font-mono border border-slate-200 rounded-xl cursor-not-allowed">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Description</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-orange-500 focus:outline-none">{{ old('description', $role->description) }}</textarea>
            </div>
        </div>

        <!-- Permissions Matrix Grouped By Module -->
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Module Permissions Matrix</h3>
                    <p class="text-xs text-slate-500">Toggle privileges granted to members with this role.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                @foreach($modules as $moduleName => $permissions)
                @php
                    $moduleKey = 'mod-' . Str::slug($moduleName);
                @endphp
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden flex flex-col justify-between">
                    
                    <!-- Module Header -->
                    <div class="p-4 bg-slate-50 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-base font-bold text-slate-800">{{ $moduleName }}</span>
                            <span class="text-[11px] font-semibold text-slate-400">({{ $permissions->count() }} rules)</span>
                        </div>

                        <div class="flex items-center gap-2 text-xs">
                            <button type="button" @click="toggleModule('{{ $moduleKey }}', true)" class="text-orange-600 hover:text-orange-800 font-semibold">All</button>
                            <span class="text-slate-300">|</span>
                            <button type="button" @click="toggleModule('{{ $moduleKey }}', false)" class="text-slate-400 hover:text-slate-600 font-semibold">None</button>
                        </div>
                    </div>

                    <!-- Permissions List -->
                    <div class="p-4 space-y-3 flex-1">
                        @foreach($permissions as $perm)
                        @php
                            $isDangerous = in_array($perm->slug, ['users.manage', 'roles.manage', 'leads.delete', 'tasks.delete']) || str_contains($perm->slug, 'delete');
                        @endphp
                        <label class="flex items-start gap-3 cursor-pointer p-2 rounded-xl hover:bg-slate-50 transition-colors">
                            <input type="checkbox" 
                                   name="permissions[]" 
                                   value="{{ $perm->id }}" 
                                   class="perm-checkbox {{ $moduleKey }} mt-0.5 w-4 h-4 text-orange-600 rounded border-slate-300 focus:ring-orange-500"
                                   {{ in_array($perm->id, $rolePermissionIds) ? 'checked' : '' }}>
                            
                            <div class="text-xs">
                                <div class="font-semibold text-slate-800 flex items-center gap-2">
                            <div class="text-xs flex-1 min-w-0">
                                <div class="font-semibold text-slate-800 flex items-center gap-2 flex-wrap">
                                    <span>{{ $perm->name }}</span>
                                    <span class="text-[10px] font-mono text-slate-400">({{ $perm->slug }})</span>
                                    @if($isDangerous)
                                        <span class="text-[9px] font-bold text-amber-700 bg-amber-50 border border-amber-200 rounded px-1">⚠️ Sensitive</span>
                                    @endif
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ $perm->description }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Sticky Save Action Bar -->
        <div class="sticky bottom-6 z-20 bg-white/95 backdrop-blur-md rounded-2xl p-4 border border-slate-200 shadow-lg flex items-center justify-between">
            <div class="text-xs text-slate-500 hidden sm:block">
                Editing role: <strong class="text-slate-900">{{ $role->name }}</strong>
            </div>

            <div class="flex items-center gap-3 ml-auto">
                <a href="{{ route('roles.index') }}" class="px-4 py-2 text-sm font-semibold text-slate-600 hover:text-slate-800">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span>Save Permissions</span>
                </button>
            </div>
        </div>
    </form>

</div>
@endsection
