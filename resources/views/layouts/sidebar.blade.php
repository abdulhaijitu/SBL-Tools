@php
    $menu = [
        ['Dashboard', 'dashboard', 'home', null, request()->routeIs('dashboard')],
        ['Leads', 'leads.index', 'users', 'leads.view', request()->routeIs('leads.*', 'tasks.*', 'presentations.*') && request('stage') !== 'converted'],
        ['Plans & Toolkit', 'toolkit.index', 'grid', 'toolkit.view', request()->routeIs('toolkit.*', 'ecosystem.*')],
        ['Contact', 'contacts.index', 'phone', 'toolkit.view', request()->routeIs('contacts.*')],
        ['Team Explorer', 'team.index', 'tree', null, request()->routeIs('team.*', 'binary.*')],
        ['Members', 'members.index', 'users', 'leads.view', request()->routeIs('members.*') || request('stage') === 'converted'],
        ['Abbreviation', 'abbreviations.index', 'book', null, request()->routeIs('abbreviations.*')],
        ['Roles & Permissions', 'roles.index', 'shield', 'roles.view', request()->routeIs('roles.*', 'users.*')],
    ];
@endphp
<nav aria-label="Main navigation" class="flex-1 overflow-y-auto px-3 py-6 space-y-1">
    <p class="px-3 pb-4 text-[11px] font-semibold tracking-widest uppercase text-slate-400">SBL Growth Manager</p>
    @foreach($menu as [$label, $route, $icon, $permission, $active])
        @if(!$permission || Auth::user()->can($permission))
            <a href="{{ route($route) }}" @if($active) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm font-medium {{ $active ? 'bg-orange-700 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}"><x-ui-icon :name="$icon" />{{ $label }}</a>
        @endif
    @endforeach
    @if(!Auth::user()->can('roles.view') && Auth::user()->can('users.view'))
        <a href="{{ route('users.index') }}" class="flex items-center gap-3 rounded-xl px-3 py-3 text-sm text-slate-300"><x-ui-icon name="shield" />User management</a>
    @endif
</nav>
