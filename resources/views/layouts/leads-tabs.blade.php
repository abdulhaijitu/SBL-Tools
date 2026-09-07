<nav aria-label="Leads sections" class="section-tabs">
    @foreach([['Leads', 'leads.index', 'leads.*', 'leads.view'], ['Tasks and Follows', 'tasks.index', 'tasks.*', 'tasks.view'], ['Presentations', 'presentations.index', 'presentations.*', 'presentations.view']] as [$label, $route, $pattern, $permission])
        @can($permission)<a href="{{ route($route) }}" @if(request()->routeIs($pattern)) aria-current="page" @endif>{{ $label }}</a>@endcan
    @endforeach
</nav>
