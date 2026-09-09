@php
    $currentTab = request('tab', 'packages');
    $isToolkit = request()->routeIs('toolkit.*');
    $isSuperAdmin = Auth::user()?->isSuperAdmin() ?? false;
@endphp
<nav aria-label="Main navigation" class="flex-1 overflow-y-auto px-3 py-5 space-y-1">
    <div class="px-3 pb-3">
        <span class="text-[10px] font-black tracking-widest uppercase text-slate-400">SBL Marketing</span>
    </div>

    <!-- 1. Dashboard -->
    <a href="{{ route('dashboard') }}" 
       @if(request()->routeIs('dashboard')) aria-current="page" @endif 
       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-orange-600 text-white shadow-sm font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
        <x-ui-icon name="home" />
        <span>Dashboard</span>
    </a>

    <!-- 2. Leads -->
    @can('leads.view')
    <a href="{{ route('leads.index') }}" 
       @if(request()->routeIs('leads.*', 'tasks.*', 'presentations.*', 'members.*')) aria-current="page" @endif 
       class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium {{ request()->routeIs('leads.*', 'tasks.*', 'presentations.*', 'members.*') ? 'bg-orange-600 text-white shadow-sm font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
        <x-ui-icon name="users" />
        <span>Leads</span>
    </a>
    @endcan

    <!-- 3. SBL Marketing Tools Section -->
    <div class="pt-4 pb-1">
        <div class="px-3 py-1 flex items-center justify-between text-[11px] font-bold tracking-wider uppercase text-orange-400">
            <span>SBL Marketing Tools</span>
        </div>
        <div class="mt-1 space-y-0.5 pl-1">
            <!-- Packages -->
            <a href="{{ route('packages.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ (request()->routeIs('packages.*') || ($isToolkit && ($currentTab === 'packages' || !$currentTab))) ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">📦</span>
                <span>Packages</span>
            </a>

            <!-- Ranks -->
            <a href="{{ route('ranks.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ (request()->routeIs('ranks.*') || ($isToolkit && in_array($currentTab, ['ranks', 'compensation']))) ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">🏆</span>
                <span>Ranks</span>
            </a>

            <!-- Counseling Guide -->
            <a href="{{ route('counseling.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ (request()->routeIs('counseling.*') || ($isToolkit && $currentTab === 'counseling')) ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">🎯</span>
                <span>Counseling Guide</span>
            </a>

            <!-- Commission -->
            <a href="{{ route('commission.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ (request()->routeIs('commission.*') || ($isToolkit && in_array($currentTab, ['commission', 'calculator']))) ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">🧮</span>
                <span>Commission</span>
            </a>

            <!-- Links (Formerly Websites) -->
            <a href="{{ route('links.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ (request()->routeIs('links.*', 'ecosystem.*') || ($isToolkit && in_array($currentTab, ['links', 'ecosystem']))) ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">🔗</span>
                <span>Links</span>
            </a>

            <!-- Resources (Leaflets & Official Documents) -->
            <a href="{{ route('resources.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ (request()->routeIs('resources.*', 'marketing-resources.*') || ($isToolkit && $currentTab === 'resources')) ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">📁</span>
                <span>Resources</span>
                <span class="ml-auto text-[9px] bg-orange-500/30 text-orange-200 px-1.5 py-0.5 rounded-md font-bold">New</span>
            </a>

            <!-- Team Explorer -->
            <a href="{{ route('team.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('team.*', 'binary.*') ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">👥</span>
                <span>Team Explorer</span>
            </a>

            <!-- Abbreviation -->
            <a href="{{ route('abbreviations.index') }}" 
               class="flex items-center gap-2.5 rounded-xl px-3 py-2 text-xs font-semibold {{ request()->routeIs('abbreviations.*') ? 'bg-orange-500/20 text-orange-300 border border-orange-500/30 font-bold' : 'text-slate-300 hover:bg-slate-800/80 hover:text-white' }}">
                <span class="text-sm">📖</span>
                <span>Abbreviation</span>
            </a>
        </div>
    </div>

    <!-- 4. Super Admin SaaS Platform Administration -->
    @if($isSuperAdmin || Auth::user()->can('users.view') || Auth::user()->can('roles.view'))
    <div class="pt-4 pb-1">
        <div class="px-3 py-1 flex items-center justify-between text-[11px] font-bold tracking-wider uppercase text-slate-400">
            <span>SaaS Administration</span>
        </div>
        <div class="mt-1 space-y-0.5">
            @if(Auth::user()->can('users.view'))
            <a href="{{ route('users.index') }}" 
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-medium {{ request()->routeIs('users.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <x-ui-icon name="users" />
                <span>Users & Accounts</span>
            </a>
            @endif

            @if(Auth::user()->can('roles.view'))
            <a href="{{ route('roles.index') }}" 
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-medium {{ request()->routeIs('roles.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <x-ui-icon name="shield" />
                <span>Roles & Permissions</span>
            </a>
            @endif

            <a href="{{ route('contacts.index') }}" 
               class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-xs font-medium {{ request()->routeIs('contacts.*') ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                <x-ui-icon name="phone" />
                <span>Contacts</span>
            </a>
        </div>
    </div>
    @endif
</nav>
