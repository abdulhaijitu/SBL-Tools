<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SBL Growth Manager') }}</title>

    <!-- Fonts & Preloads -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="preload" as="image" href="{{ asset('images/sbl-logo.webp') }}">

    <!-- Favicon & App Icons -->
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full font-sans antialiased text-slate-900 selection:bg-orange-500 selection:text-white" 
      x-data="{ 
        sidebarOpen: false, 
        quickActionOpen: false,
        toasts: [],
        addToast(message, type = 'success') {
            const id = Date.now() + Math.random();
            this.toasts.push({ id, message, type });
            setTimeout(() => this.removeToast(id), 4000);
        },
        removeToast(id) {
            this.toasts = this.toasts.filter(t => t.id !== id);
        }
      }"
      x-init="@if(session('success')) addToast('{{ addslashes(session('success')) }}', 'success'); @endif @if(session('error')) addToast('{{ addslashes(session('error')) }}', 'error'); @endif"
      @notify.window="addToast($event.detail.message, $event.detail.type || 'success')">

    <!-- Global Floating Toast Notification Stack -->
    <div class="fixed top-4 right-4 z-50 space-y-2 max-w-sm w-full pointer-events-none px-4 sm:px-0">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 -translate-y-2 scale-95"
                 :class="toast.type === 'error' ? 'bg-rose-950/95 border-rose-700/80 text-rose-100' : 'bg-slate-950/95 border-slate-700/80 text-white'"
                 class="pointer-events-auto p-3.5 rounded-2xl shadow-2xl border flex items-center justify-between gap-3 text-xs font-semibold backdrop-blur-md">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="w-6 h-6 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0"
                          :class="toast.type === 'error' ? 'bg-rose-500/20 text-rose-400' : 'bg-emerald-500/20 text-emerald-400'"
                          x-text="toast.type === 'error' ? '⚠️' : '✓'"></span>
                    <span class="truncate leading-tight" x-text="toast.message"></span>
                </div>
                <button @click="removeToast(toast.id)" class="text-slate-400 hover:text-white p-1 flex-shrink-0">&times;</button>
            </div>
        </template>
    </div>

    <div class="min-h-full flex flex-col md:flex-row">

        <!-- Mobile Off-Canvas Backdrop -->
        <div x-show="sidebarOpen" 
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm md:hidden"
             @click="sidebarOpen = false"
             x-cloak></div>

        <!-- Sidebar (Desktop & Mobile Off-Canvas) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-950 text-slate-200 flex flex-col transition-transform duration-200 ease-in-out md:static md:translate-x-0 border-r border-slate-800">
            
            <!-- Brand / Logo Header -->
            <div class="h-20 px-4 flex items-center justify-between border-b border-slate-800/80 bg-slate-950">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                    <img src="{{ asset('images/sbl-logo.webp') }}" alt="SBL The Ecommerce Ecosystem" class="h-14 w-auto max-w-[200px] object-contain">
                </a>
                <button @click="sidebarOpen = false" class="md:hidden text-slate-400 hover:text-white p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    <span>Dashboard</span>
                </a>

                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">CRM Pipeline</div>
                <a href="{{ route('leads.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('leads.*') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span>Leads CRM</span>
                </a>

                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Activities</div>
                <a href="{{ route('tasks.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('tasks.*') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                    <span>Tasks & Follow-ups</span>
                </a>
                <a href="{{ route('presentations.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('presentations.*') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"></path></svg>
                    <span>Presentations</span>
                </a>

                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Marketing</div>
                <a href="{{ route('marketing.content-calendar') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('marketing.content-calendar') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span>Content Calendar</span>
                </a>

                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Analytics & Toolkit</div>
                <a href="{{ route('reports.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('reports.*') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    <span>Funnel Reports</span>
                </a>
                <a href="{{ route('toolkit.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('toolkit.*') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <span>SBL Plans & Toolkit</span>
                </a>

                @if(!Auth::user() || Auth::user()->can('users.view') || Auth::user()->hasRole(['super-admin', 'sales-manager']))
                <div class="pt-4 pb-1 px-3 text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Administration</div>
                <a href="{{ route('users.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('users.*') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    <span>Team & Users</span>
                </a>
                <a href="{{ route('roles.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('roles.*') ? 'bg-orange-600 text-white font-semibold shadow-sm' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span>Roles & Permissions</span>
                </a>
                @endif
            </nav>

            <!-- User Footer in Sidebar -->
            <div class="p-3 border-t border-slate-800/80 bg-slate-950/80 flex items-center justify-between">
                <div class="flex items-center gap-3 overflow-hidden">
                    <div class="w-8 h-8 rounded-full bg-slate-800 text-orange-400 font-bold flex items-center justify-center text-sm border border-slate-700">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="truncate">
                        <div class="text-xs font-semibold text-white truncate">{{ Auth::user()->name ?? 'Admin' }}</div>
                        <div class="text-[10px] text-orange-400 font-medium truncate">{{ Auth::user()->primary_role->name ?? 'Administrator' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Logout" class="p-1.5 text-slate-400 hover:text-red-400 hover:bg-slate-900 rounded-lg transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Body Area -->
        <div class="flex-1 flex flex-col min-w-0 pb-20 md:pb-6">

            <!-- Mobile & Desktop Top Bar -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 sticky top-0 z-30 md:px-8">
                <div class="flex items-center gap-2.5">
                    <button @click="sidebarOpen = true" class="md:hidden p-1.5 text-slate-600 hover:text-slate-900 hover:bg-slate-100 rounded-lg focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                    <a href="{{ route('dashboard') }}" class="md:hidden flex-shrink-0">
                        <img src="{{ asset('images/sbl-logo.webp') }}" alt="SBL" class="h-8 w-auto object-contain bg-slate-950 p-1 rounded-lg border border-slate-800">
                    </a>
                    <div>
                        <h1 class="text-base sm:text-lg font-bold text-slate-900 tracking-tight leading-none truncate max-w-[200px] sm:max-w-md">
                            @yield('page-title', 'Dashboard')
                        </h1>
                        <p class="text-xs text-slate-500 mt-0.5 hidden sm:block">
                            @yield('page-subtitle', 'SBL Growth Management Ecosystem')
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 sm:gap-3">
                    <!-- Quick Add Lead Button (Desktop) -->
                    <a href="{{ route('leads.create') }}" 
                       class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-xs font-semibold shadow-sm transition-all active:scale-95">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>New Lead (<30s)</span>
                    </a>

                    <div class="h-4 w-px bg-slate-200 mx-1 hidden sm:block"></div>

                    <!-- User Profile Dropdown -->
                    <a href="{{ route('profile.edit') }}" class="flex items-center gap-2 p-1.5 text-slate-600 hover:text-slate-900 rounded-lg hover:bg-slate-100 text-xs font-medium">
                        <div class="w-7 h-7 rounded-full bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs">
                            {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                        </div>
                        <span class="hidden md:inline">{{ Auth::user()->name ?? 'Admin' }}</span>
                    </a>
                </div>
            </header>

            <!-- Page Content View -->
            <main class="flex-1 p-4 md:p-8 overflow-y-auto">
                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>

        <!-- Mobile Bottom Navigation Bar (Section 24 - Native Mobile UX) -->
        <nav class="md:hidden fixed bottom-0 inset-x-0 z-40 bg-white/95 backdrop-blur-md border-t border-slate-200/80 px-2 py-1.5 flex items-center justify-around shadow-lg">
            
            <!-- 1. Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('dashboard') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                <span class="text-[10px]">Dashboard</span>
            </a>

            <!-- 2. Leads CRM -->
            <a href="{{ route('leads.index') }}" 
               class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('leads.*') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span class="text-[10px]">Leads</span>
            </a>

            <!-- 3. Center FAB Trigger -->
            <button @click="quickActionOpen = !quickActionOpen" 
                    type="button" 
                    aria-label="Quick Action"
                    class="-mt-5 w-12 h-12 rounded-full bg-gradient-to-tr from-orange-600 to-orange-500 hover:from-orange-700 hover:to-orange-600 active:scale-90 text-white flex items-center justify-center shadow-lg shadow-orange-600/40 border-2 border-white focus:outline-none transition-transform">
                <svg :class="quickActionOpen ? 'rotate-45' : 'rotate-0'" class="w-6 h-6 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"></path></svg>
            </button>

            <!-- 4. Tasks -->
            <a href="{{ route('tasks.index') }}" 
               class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('tasks.*') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                <span class="text-[10px]">Tasks</span>
            </a>

            <!-- 5. Toolkit & Plans -->
            <a href="{{ route('toolkit.index') }}" 
               class="flex flex-col items-center gap-0.5 py-1 px-2.5 rounded-xl transition-all {{ request()->routeIs('toolkit.*') ? 'text-orange-600 font-bold' : 'text-slate-500 hover:text-slate-900' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                <span class="text-[10px]">Toolkit</span>
            </a>
        </nav>

        <!-- Floating Quick Action Button for Desktop Only -->
        <div class="hidden md:block fixed bottom-6 right-6 z-40">
            <!-- Floating Menu Popup -->
            <div x-show="quickActionOpen"
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                 x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                 @click.outside="quickActionOpen = false"
                 class="mb-3 w-56 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-2 text-white text-sm"
                 x-cloak>
                <div class="px-3 py-1.5 text-[11px] font-bold text-orange-400 uppercase tracking-wider border-b border-slate-800 mb-1">
                    Quick Actions
                </div>
                <a href="{{ route('leads.create') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-800 transition-colors">
                    <span class="w-6 h-6 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">+</span>
                    <span>Add New Lead</span>
                </a>
                <a href="{{ route('tasks.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-800 transition-colors">
                    <span class="w-6 h-6 rounded-lg bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-xs">✓</span>
                    <span>Schedule Task</span>
                </a>
                <a href="{{ route('presentations.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl hover:bg-slate-800 transition-colors">
                    <span class="w-6 h-6 rounded-lg bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-xs">P</span>
                    <span>Add Presentation</span>
                </a>
            </div>

            <!-- Primary FAB Button -->
            <button @click="quickActionOpen = !quickActionOpen" 
                    type="button" 
                    aria-label="Quick Action"
                    class="w-14 h-14 rounded-full bg-orange-600 hover:bg-orange-700 active:scale-95 text-white flex items-center justify-center shadow-xl shadow-orange-600/40 transition-transform duration-200 focus:outline-none focus:ring-4 focus:ring-orange-600/30">
                <svg :class="quickActionOpen ? 'rotate-45' : 'rotate-0'" class="w-7 h-7 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"></path></svg>
            </button>
        </div>

        <!-- Mobile Quick Action Popup Menu (from center bottom bar) -->
        <div x-show="quickActionOpen"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-4 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 scale-95"
             @click.outside="quickActionOpen = false"
             class="md:hidden fixed bottom-20 inset-x-4 z-50 bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-2.5 text-white text-sm"
             x-cloak>
            <div class="px-3 py-1.5 text-[11px] font-bold text-orange-400 uppercase tracking-wider border-b border-slate-800 mb-1 flex items-center justify-between">
                <span>Quick Actions (<30s)</span>
                <button @click="quickActionOpen = false" class="text-slate-400 hover:text-white">&times;</button>
            </div>
            <div class="grid grid-cols-3 gap-1.5 pt-1">
                <a href="{{ route('leads.create') }}" class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition-colors text-center">
                    <span class="w-8 h-8 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-sm mb-1">+</span>
                    <span class="text-xs font-semibold">New Lead</span>
                </a>
                <a href="{{ route('tasks.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition-colors text-center">
                    <span class="w-8 h-8 rounded-xl bg-blue-500/20 text-blue-400 flex items-center justify-center font-bold text-sm mb-1">✓</span>
                    <span class="text-xs font-semibold">New Task</span>
                </a>
                <a href="{{ route('presentations.index') }}" class="flex flex-col items-center justify-center p-3 rounded-xl bg-slate-800/80 hover:bg-slate-800 transition-colors text-center">
                    <span class="w-8 h-8 rounded-xl bg-purple-500/20 text-purple-400 flex items-center justify-center font-bold text-sm mb-1">P</span>
                    <span class="text-xs font-semibold">Pitch</span>
                </a>
            </div>
        </div>

    </div>
</body>
</html>
