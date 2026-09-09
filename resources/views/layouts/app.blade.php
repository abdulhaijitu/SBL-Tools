<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-currency="{{ \App\Services\CurrencyService::getCurrency() }}" data-exchange-rate="{{ \App\Services\CurrencyService::BDT_RATE }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#f97316">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="SBL Tools">
    <link rel="manifest" href="/manifest.webmanifest">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <title>@yield('page-title', 'Profile') · SBL Growth Manager</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preload" as="image" href="{{ asset('images/sbl-logo.webp') }}" type="image/webp" fetchpriority="high">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="app-body" x-data="appShell" :class="{'navigation-open': sidebarOpen}" @keydown.escape.window="closeMenus()" @notify.window="addToast($event.detail.message, $event.detail.type || 'success')">
    <a class="skip-link" href="#main-content">Skip to content</a>
    <div class="app-backdrop" x-show="sidebarOpen" x-cloak @click="closeMenus()"></div>
    <aside id="main-sidebar" class="app-sidebar" :data-open="sidebarOpen" :inert="!sidebarOpen && isMobile">
        <div class="app-brand">
            <a href="{{ route('dashboard') }}" aria-label="SBL Growth Manager home"><img src="{{ asset('images/sbl-logo.webp') }}" alt="SBL" width="142" height="48" fetchpriority="high" decoding="async"></a>
            <button type="button" class="icon-button lg:hidden" aria-label="Close navigation" @click="closeMenus()"><x-ui-icon name="close" /></button>
        </div>
        @include('layouts.sidebar')
        <div class="app-sidebar-footer">
            <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1"><strong class="block truncate text-sm text-white">{{ Auth::user()->name }}</strong><span class="text-xs text-slate-400">{{ Auth::user()->primary_role->name ?? 'Member' }}</span></a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="icon-button" aria-label="Sign out"><x-ui-icon name="logout" /></button></form>
        </div>
    </aside>
    <div class="app-workspace">
        <header class="app-header">
            <div class="flex min-w-0 items-center gap-2">
                <button type="button" x-ref="menuButton" class="icon-button lg:hidden" aria-label="Open navigation" aria-controls="main-sidebar" :aria-expanded="sidebarOpen" @click="sidebarOpen = true; $nextTick(() => document.querySelector('#main-sidebar button').focus())"><x-ui-icon name="menu" /></button>
                <h1 id="app-page-title">@yield('page-title', 'Profile')</h1>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <label class="sr-only" for="display-currency">Display currency</label>
                <select id="display-currency" class="currency-select" :value="$store.currency.code" :disabled="$store.currency.busy" @change="$store.currency.set($event.target.value)">
                    <option value="USD">USD</option><option value="BDT">BDT</option>
                </select>
                @can('leads.create')<a href="{{ route('leads.create') }}" class="btn-primary hidden sm:inline-flex"><x-ui-icon name="plus" /> New lead</a>@endcan
                <a href="{{ route('profile.edit') }}" class="profile-avatar" aria-label="Your profile">{{ mb_substr(Auth::user()->name, 0, 1) }}</a>
            </div>
        </header>
        <main id="main-content" class="app-main" tabindex="-1">
            @if(session('success'))<div class="app-notice" role="status">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="app-notice app-notice-error" role="alert">{{ session('error') }}</div>@endif
            @if($errors->any())
                <div class="app-notice app-notice-error" role="alert"><strong>Please check the following:</strong><ul class="mt-2 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if(request()->routeIs('leads.*', 'tasks.*', 'presentations.*') && request('stage') !== 'converted')@include('layouts.leads-tabs')@endif
            {{ $slot ?? '' }}
            @yield('content')
        </main>
    </div>
    <nav class="mobile-navigation" aria-label="Mobile navigation">
        <a href="{{ route('dashboard') }}" @if(request()->routeIs('dashboard')) aria-current="page" @endif><x-ui-icon name="home" /><span>Home</span></a>
        @can('leads.view')<a href="{{ route('leads.index') }}" @if(request()->routeIs('leads.*', 'tasks.*', 'presentations.*')) aria-current="page" @endif><x-ui-icon name="users" /><span>Leads</span></a>@endcan
        @can('leads.create')<a href="{{ route('leads.create') }}" class="mobile-add" aria-label="Add a lead"><x-ui-icon name="plus" /></a>@endcan
        <a href="{{ route('team.index') }}" @if(request()->routeIs('team.*', 'binary.*')) aria-current="page" @endif><x-ui-icon name="tree" /><span>Team</span></a>
        <button type="button" @click="sidebarOpen = true" aria-label="All pages" aria-controls="main-sidebar" :aria-expanded="sidebarOpen"><x-ui-icon name="menu" /><span>More</span></button>
    </nav>
    <div class="toast-stack" aria-live="polite" aria-atomic="false"><template x-for="toast in toasts" :key="toast.id"><div class="app-notice shadow-lg" :class="{'app-notice-error': toast.type === 'error'}"><span x-text="toast.message"></span><button type="button" class="icon-button" aria-label="Dismiss notification" @click="removeToast(toast.id)"><x-ui-icon name="close" /></button></div></template></div>
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js').catch(function(err) {
                    console.log('SW register failed: ', err);
                });
            });
        }
    </script>
</body>
</html>
