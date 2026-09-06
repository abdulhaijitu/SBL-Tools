const fs = require('fs');
const path = require('path');

const logoBase64 = fs.readFileSync(path.join(__dirname, 'storage', 'logo_base64.txt'), 'utf8').trim();

const workerCode = `// SBL Growth Manager - Cloudflare Worker Edge Application
// High-performance, zero-latency edge deployment for Cloudflare Workers

const SBL_LOGO_BASE64 = "${logoBase64}";

export default {
  async fetch(request, env, ctx) {
    const url = new URL(request.url);

    // Serve binary logo if requested
    if (url.pathname === '/images/sbl-logo.webp') {
      const binaryString = atob(SBL_LOGO_BASE64);
      const len = binaryString.length;
      const bytes = new Uint8Array(len);
      for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
      }
      return new Response(bytes.buffer, {
        headers: {
          'Content-Type': 'image/webp',
          'Cache-Control': 'public, max-age=31536000, immutable'
        }
      });
    }

    // Health / ping
    if (url.pathname === '/ping') {
      return new Response('pong', { status: 200 });
    }

    // Return the full-stack SBL Growth Manager & Toolkit Web Application
    return new Response(getAppHtml(), {
      headers: {
        'Content-Type': 'text/html; charset=utf-8',
        'Cache-Control': 'public, max-age=0, must-revalidate',
        'X-Powered-By': 'Cloudflare Workers Edge'
      }
    });
  }
};

function getAppHtml() {
  return \`<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SBL Growth Manager & Toolkit - Cloudflare Live</title>
    <link rel="icon" href="data:image/webp;base64,\${SBL_LOGO_BASE64}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        orange: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                            800: '#9a3412',
                            900: '#7c2d12',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <!-- SortableJS -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .tap-highlight-none { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="h-full font-sans antialiased text-slate-800 bg-slate-50 selection:bg-orange-500 selection:text-white" x-data="sblApp()" x-init="initApp()">

    <!-- Main Navigation Header -->
    <header class="sticky top-0 z-30 bg-slate-950 border-b border-slate-800 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-3">
                    <img src="data:image/webp;base64,\${SBL_LOGO_BASE64}" alt="SBL Logo" class="w-9 h-9 rounded-xl shadow-xs border border-white/10 object-contain bg-white/5 p-0.5">
                    <div>
                        <span class="text-base font-extrabold text-white tracking-tight flex items-center gap-1.5">
                            SBL <span class="text-orange-500">GROWTH</span>
                        </span>
                        <span class="text-[10px] text-slate-400 block -mt-1 font-medium tracking-wide">Edge Edition • Workers Live</span>
                    </div>
                </div>

                <!-- Desktop Nav Links -->
                <nav class="hidden md:flex items-center gap-1">
                    <button @click="setTab('dashboard')" :class="currentTab === 'dashboard' ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        📊 Dashboard
                    </button>
                    <button @click="setTab('leads')" :class="currentTab === 'leads' ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        👥 Leads CRM
                    </button>
                    <button @click="setTab('kanban')" :class="currentTab === 'kanban' ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        📋 Kanban Pipeline
                    </button>
                    <button @click="setTab('calculator')" :class="currentTab === 'calculator' ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        🧮 ROI & 10-Gen Calculator
                    </button>
                    <button @click="setTab('toolkit')" :class="currentTab === 'toolkit' ? 'bg-slate-800 text-white font-bold' : 'text-slate-300 hover:bg-slate-800/50 hover:text-white'" class="px-3.5 py-2 rounded-xl text-xs font-semibold transition-all">
                        📖 Plans & Toolkit
                    </button>
                </nav>

                <!-- Header Actions -->
                <div class="flex items-center gap-2">
                    <button @click="openAddLeadModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 active:scale-95 text-white font-bold text-xs shadow-xs transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        <span>Add Lead</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Global Floating Toast Notification -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="fixed top-20 right-4 sm:right-6 z-50 bg-slate-950 text-white text-xs px-4 py-3 rounded-2xl shadow-2xl border border-slate-800 flex items-center gap-2.5 max-w-sm"
         x-cloak>
        <span class="w-2.5 h-2.5 rounded-full bg-orange-500 animate-ping"></span>
        <span x-text="toast.message" class="font-semibold text-slate-100"></span>
    </div>

    <!-- Main Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 pb-24 md:pb-8">

        <!-- ============================================== -->
        <!-- TAB 1: DASHBOARD VIEW                          -->
        <!-- ============================================== -->
        <div x-show="currentTab === 'dashboard'" x-cloak class="space-y-6">
            
            <!-- Page Title -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">Executive Dashboard</h1>
                    <p class="text-xs text-slate-500 mt-0.5">SBL Growth System • Operations & Daily Pipeline Priorities</p>
                </div>
                <div class="flex items-center gap-2 self-start sm:self-auto">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Edge Live Active
                    </span>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Follow-ups Today -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Follow-ups Today</span>
                        <span class="text-2xl font-black text-slate-900 mt-1 block" x-text="getDueTodayLeads().length"></span>
                        <span class="text-[11px] text-slate-400">Scheduled for today</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-orange-50 text-orange-600 flex items-center justify-center font-bold text-lg">
                        ⏰
                    </div>
                </div>

                <!-- Overdue Follow-ups -->
                <div class="bg-white rounded-2xl p-4 border border-rose-200 bg-rose-50/20 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider block">Overdue Follow-ups</span>
                        <span class="text-2xl font-black text-rose-700 mt-1 block" x-text="getOverdueLeads().length"></span>
                        <span class="text-[11px] text-slate-400">Requires urgent touch</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-600 flex items-center justify-center font-bold text-lg">
                        🚨
                    </div>
                </div>

                <!-- Presentations Today -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Presentations</span>
                        <span class="text-2xl font-black text-slate-900 mt-1 block" x-text="getStageCount('presentation')"></span>
                        <span class="text-[11px] text-slate-400">Active sessions</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center font-bold text-lg">
                        📊
                    </div>
                </div>

                <!-- Total Active Leads -->
                <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Leads</span>
                        <span class="text-2xl font-black text-slate-900 mt-1 block" x-text="leads.length"></span>
                        <span class="text-[11px] text-slate-400">Active pipeline</span>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-lg">
                        👥
                    </div>
                </div>
            </div>

            <!-- Pipeline Funnel -->
            <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Lead Pipeline Funnel</h3>
                        <p class="text-xs text-slate-500">Continuous conversion progress from initial contact to conversion</p>
                    </div>
                    <button @click="setTab('kanban')" class="text-xs font-semibold text-orange-600 hover:text-orange-700 flex items-center gap-1">
                        <span>Open Kanban Board</span>
                        <span>→</span>
                    </button>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-2">
                    <template x-for="stage in stages" :key="stage.key">
                        <button @click="filterStage(stage.key)" class="bg-slate-50 hover:bg-orange-50/50 hover:border-orange-200 border border-slate-100 rounded-xl p-3 text-center transition-all group active:scale-95 flex flex-col justify-between">
                            <div>
                                <span class="text-[11px] font-semibold text-slate-500 group-hover:text-orange-700 uppercase tracking-tight block truncate" x-text="stage.label"></span>
                                <span class="text-xl font-black text-slate-900 group-hover:text-orange-600 mt-1 block" x-text="getStageCount(stage.key)"></span>
                            </div>
                            <div class="w-full bg-slate-200/80 h-1 rounded-full mt-2 overflow-hidden">
                                <div class="bg-orange-500 h-full rounded-full transition-all duration-500" :style="'width: ' + (leads.length > 0 ? Math.min(100, Math.round((getStageCount(stage.key) / leads.length) * 100)) : 0) + '%'"></div>
                            </div>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Split Section: Overdue & Hot Leads -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Urgent Overdue List -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full bg-rose-500 animate-pulse"></span>
                            <h3 class="text-sm font-bold text-slate-900">Immediate Follow-up Needed (Overdue)</h3>
                        </div>
                        <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-md" x-text="getOverdueLeads().length + ' Overdue'"></span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        <template x-for="lead in getOverdueLeads()" :key="lead.id">
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50 transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0" x-text="lead.name.charAt(0)"></div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-sm text-slate-900" x-text="lead.name"></span>
                                            <span class="px-2 py-0.5 text-[10px] font-bold rounded-full border border-red-200 bg-red-50 text-red-700" x-text="lead.temperature"></span>
                                        </div>
                                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-3">
                                            <span x-text="'📞 ' + lead.mobile"></span>
                                            <span class="text-rose-600 font-bold" x-text="'Overdue: ' + lead.next_action_type"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 self-end sm:self-center">
                                    <a :href="'tel:' + lead.mobile" class="px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold text-xs border border-emerald-200">
                                        📞 Call
                                    </a>
                                    <a :href="'https://wa.me/' + cleanPhone(lead.whatsapp || lead.mobile)" target="_blank" class="px-3 py-1.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs">
                                        💬 WhatsApp
                                    </a>
                                </div>
                            </div>
                        </template>
                        <div x-show="getOverdueLeads().length === 0" class="p-8 text-center text-slate-400 text-xs font-medium">
                            🎉 Excellent! No overdue follow-ups right now.
                        </div>
                    </div>
                </div>

                <!-- Hot Priority Leads -->
                <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                            <span>🔥</span> Hot Priority Leads
                        </h3>
                        <span class="text-xs font-semibold text-slate-400" x-text="getHotLeads().length + ' hot'"></span>
                    </div>

                    <div class="space-y-2.5">
                        <template x-for="lead in getHotLeads()" :key="lead.id">
                            <div class="p-3 rounded-xl border border-slate-100 hover:border-orange-200 hover:bg-orange-50/30 transition-all flex items-center justify-between">
                                <div>
                                    <div class="font-bold text-xs text-slate-900" x-text="lead.name"></div>
                                    <div class="text-[11px] text-slate-500 mt-0.5" x-text="getStageLabel(lead.stage) + ' • ' + lead.mobile"></div>
                                </div>
                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-lg bg-orange-600 text-white" x-text="lead.score + ' pts'"></span>
                            </div>
                        </template>
                        <div x-show="getHotLeads().length === 0" class="py-6 text-center text-slate-400 text-xs">
                            No hot leads currently.
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 2: LEADS CRM TABLE & MOBILE CARDS          -->
        <!-- ============================================== -->
        <div x-show="currentTab === 'leads'" x-cloak class="space-y-4">
            
            <!-- Header & Filter Bar -->
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs space-y-3">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <!-- Search -->
                    <div class="relative flex-1">
                        <input type="text" x-model="searchQuery" placeholder="Search leads by name, mobile, location..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-300 focus:border-orange-500 bg-slate-50/50">
                        <span class="absolute left-3 top-2.5 text-slate-400 text-xs">🔍</span>
                    </div>

                    <!-- View Switcher & Action -->
                    <div class="flex items-center gap-2 self-end sm:self-auto">
                        <div class="inline-flex rounded-xl bg-slate-100 p-1 border border-slate-200">
                            <button @click="setTab('leads')" class="px-3 py-1 text-xs font-semibold rounded-lg bg-white text-slate-900 shadow-xs">Table</button>
                            <button @click="setTab('kanban')" class="px-3 py-1 text-xs font-semibold rounded-lg text-slate-600 hover:text-slate-900">Kanban</button>
                        </div>
                        <button @click="openAddLeadModal()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-xs font-bold shadow-xs">
                            + Add Lead
                        </button>
                    </div>
                </div>

                <!-- Filter Pills -->
                <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
                    <span class="text-slate-400 font-bold text-[10px] uppercase tracking-wider flex-shrink-0">Filters:</span>
                    <button @click="currentFilter = 'all'" :class="currentFilter === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100'" class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0">
                        All Leads (<span x-text="leads.length"></span>)
                    </button>
                    <button @click="currentFilter = 'overdue'" :class="currentFilter === 'overdue' ? 'bg-rose-600 text-white border-rose-600' : 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100'" class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0">
                        🚨 Overdue (<span x-text="getOverdueLeads().length"></span>)
                    </button>
                    <button @click="currentFilter = 'due_today'" :class="currentFilter === 'due_today' ? 'bg-orange-600 text-white border-orange-600' : 'bg-orange-50 text-orange-700 border-orange-200 hover:bg-orange-100'" class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0">
                        ⏰ Due Today (<span x-text="getDueTodayLeads().length"></span>)
                    </button>
                    <button @click="currentFilter = 'hot'" :class="currentFilter === 'hot' ? 'bg-red-600 text-white border-red-600' : 'bg-red-50 text-red-700 border-red-200 hover:bg-red-100'" class="px-2.5 py-1 rounded-lg border font-medium flex-shrink-0">
                        🔥 Hot (<span x-text="getHotLeads().length"></span>)
                    </button>
                </div>
            </div>

            <!-- Leads List: Desktop Table & Mobile Card Stack -->
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
                <!-- Desktop Table -->
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3.5 px-4">Lead Info</th>
                                <th class="py-3.5 px-4">Contact</th>
                                <th class="py-3.5 px-4">Source & Tag</th>
                                <th class="py-3.5 px-4">Stage</th>
                                <th class="py-3.5 px-4">Score / Temp</th>
                                <th class="py-3.5 px-4">Next Action</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="lead in filteredLeads()" :key="lead.id">
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-9 h-9 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-xs flex-shrink-0" x-text="lead.name.charAt(0)"></div>
                                            <div>
                                                <div class="font-bold text-slate-900 text-sm" x-text="lead.name"></div>
                                                <div class="text-[11px] text-slate-400" x-text="lead.location || 'No location'"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-slate-800" x-text="lead.mobile"></div>
                                        <div class="flex items-center gap-2 mt-1">
                                            <a :href="'tel:' + lead.mobile" title="Call" class="text-slate-400 hover:text-emerald-600 text-sm">📞</a>
                                            <a :href="'https://wa.me/' + cleanPhone(lead.whatsapp || lead.mobile)" target="_blank" title="WhatsApp" class="text-slate-400 hover:text-emerald-600 text-sm">💬</a>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="font-medium text-slate-800 block" x-text="lead.source"></span>
                                        <span x-show="lead.tag" class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-800 text-[10px] font-bold mt-1 inline-block" x-text="lead.tag"></span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <select @change="updateLeadStage(lead.id, $event.target.value)" class="text-[11px] font-bold rounded-lg border border-slate-200 px-2 py-1 bg-slate-50 text-slate-800">
                                            <template x-for="st in stages" :key="st.key">
                                                <option :value="st.key" :selected="lead.stage === st.key" x-text="st.label"></option>
                                            </template>
                                        </select>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold text-slate-900" x-text="lead.score + '/100'"></span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border border-orange-200 bg-orange-50 text-orange-700" x-text="lead.temperature"></span>
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-medium text-slate-800" x-text="lead.next_action_type || 'Follow-up Call'"></div>
                                        <div class="text-[11px]" :class="lead.is_overdue ? 'text-rose-600 font-bold' : 'text-slate-400'" x-text="lead.next_action_date + (lead.is_overdue ? ' (Overdue)' : '')"></div>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <button @click="deleteLead(lead.id)" class="text-rose-500 hover:text-rose-700 text-xs font-semibold">Delete</button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Card Stack -->
                <div class="block md:hidden divide-y divide-slate-100">
                    <template x-for="lead in filteredLeads()" :key="lead.id">
                        <div class="p-4 space-y-3 hover:bg-slate-50/50 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-700 font-bold flex items-center justify-center text-sm flex-shrink-0 shadow-xs" x-text="lead.name.charAt(0)"></div>
                                    <div>
                                        <div class="font-bold text-slate-900 text-sm" x-text="lead.name"></div>
                                        <div class="text-[11px] text-slate-400" x-text="(lead.location || 'Dhaka') + ' • ' + lead.source"></div>
                                    </div>
                                </div>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border border-slate-200 bg-slate-50 text-slate-700" x-text="getStageLabel(lead.stage)"></span>
                            </div>

                            <div class="flex flex-wrap items-center gap-1.5 text-[11px]">
                                <span class="px-2 py-0.5 rounded-full font-bold border border-orange-200 bg-orange-50 text-orange-800" x-text="lead.temperature + ' (' + lead.score + ' pts)'"></span>
                                <span x-show="lead.tag" class="px-1.5 py-0.5 rounded bg-orange-100 text-orange-800 font-bold" x-text="lead.tag"></span>
                            </div>

                            <div class="bg-slate-50 rounded-xl p-2.5 flex items-center justify-between text-xs border border-slate-100">
                                <div class="flex items-center gap-1.5">
                                    <span x-text="lead.is_overdue ? '🚨' : '⏰'"></span>
                                    <div>
                                        <span class="font-semibold" :class="lead.is_overdue ? 'text-rose-600 font-bold' : 'text-slate-700'" x-text="lead.next_action_type || 'Follow-up'"></span>
                                        <span class="text-[11px] text-slate-400" x-text="'• ' + lead.next_action_date"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Mobile Action Buttons -->
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <a :href="'tel:' + lead.mobile" class="py-2 px-2 rounded-xl bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 text-emerald-800 font-bold text-xs text-center flex items-center justify-center gap-1.5 active:scale-95 transition-all">
                                    <span>📞 Call</span>
                                </a>
                                <a :href="'https://wa.me/' + cleanPhone(lead.whatsapp || lead.mobile)" target="_blank" class="py-2 px-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs text-center flex items-center justify-center gap-1.5 shadow-xs active:scale-95 transition-all">
                                    <span>💬 WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 3: KANBAN PIPELINE BOARD                   -->
        <!-- ============================================== -->
        <div x-show="currentTab === 'kanban'" x-cloak class="space-y-4">
            
            <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-slate-900">Drag & Drop Pipeline Stages</h3>
                    <p class="text-xs text-slate-500">Move cards between stages to update progress.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="setTab('leads')" class="px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-200 hover:bg-slate-50">Table View</button>
                    <button @click="openAddLeadModal()" class="px-3.5 py-1.5 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs">+ Add Lead</button>
                </div>
            </div>

            <!-- Mobile Quick Jump Bar -->
            <div class="block md:hidden bg-white rounded-2xl p-2.5 border border-slate-200/80 shadow-xs">
                <div class="flex items-center gap-1.5 overflow-x-auto no-scrollbar text-[11px]">
                    <span class="text-slate-400 font-bold uppercase text-[10px] pl-1 pr-1 flex-shrink-0">Jump:</span>
                    <template x-for="stage in stages" :key="stage.key">
                        <button @click="scrollKanbanTo(stage.key)" class="px-2.5 py-1 rounded-lg border border-slate-200 bg-slate-50 font-semibold text-slate-700 flex-shrink-0 flex items-center gap-1">
                            <span x-text="stage.label"></span>
                            <span class="px-1.5 py-0.2 rounded-full bg-white text-slate-900 border text-[10px] font-bold" x-text="getStageCount(stage.key)"></span>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Horizontal Kanban Board -->
            <div class="flex items-start gap-3 overflow-x-auto pb-6 min-h-[calc(100vh-230px)] no-scrollbar" id="kanban-wrapper">
                <template x-for="stage in stages" :key="stage.key">
                    <div :id="'col-' + stage.key" class="w-72 flex-shrink-0 bg-slate-100/80 rounded-2xl border border-slate-200/80 p-3 flex flex-col max-h-[calc(100vh-230px)]">
                        <!-- Column Header -->
                        <div class="flex items-center justify-between mb-3 px-1">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" :class="stage.dotColor"></span>
                                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-tight" x-text="stage.label"></h4>
                            </div>
                            <span class="text-xs font-bold text-slate-500 bg-white px-2 py-0.5 rounded-full border border-slate-200" x-text="getStageCount(stage.key)"></span>
                        </div>

                        <!-- Card List -->
                        <div class="space-y-2.5 overflow-y-auto flex-1 pr-1 kanban-column" :data-stage="stage.key">
                            <template x-for="lead in getLeadsInStage(stage.key)" :key="lead.id">
                                <div class="bg-white rounded-xl p-3.5 border border-slate-200/90 shadow-xs hover:border-orange-300 hover:shadow-md transition-all cursor-grab active:cursor-grabbing kanban-card" :data-id="lead.id">
                                    <div class="flex items-start justify-between gap-2">
                                        <span class="font-bold text-sm text-slate-900" x-text="lead.name"></span>
                                        <span class="px-1.5 py-0.5 text-[10px] font-bold rounded border border-orange-200 bg-orange-50 text-orange-700 flex-shrink-0" x-text="lead.temperature"></span>
                                    </div>
                                    <div class="text-xs text-slate-500 mt-1 flex items-center justify-between">
                                        <span x-text="'📞 ' + lead.mobile"></span>
                                        <span class="font-bold text-slate-700" x-text="lead.score + ' pts'"></span>
                                    </div>
                                    <div class="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px]">
                                        <span class="text-slate-400 truncate max-w-[110px]" x-text="lead.source"></span>
                                        <span class="font-semibold" :class="lead.is_overdue ? 'text-rose-600' : 'text-slate-500'" x-text="lead.next_action_type || 'Follow-up'"></span>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 4: LIVE ROI & 10-GEN COMMISSION CALCULATOR -->
        <!-- ============================================== -->
        <div x-show="currentTab === 'calculator'" x-cloak class="space-y-6">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 pb-4">
                <div>
                    <h2 class="text-xl font-black text-slate-900 tracking-tight">Live ROI & 10-Generation Commission Calculator</h2>
                    <p class="text-xs text-slate-500">Official SBL Growth Formula • Transparent Earnings & Matrix Simulator</p>
                </div>
            </div>

            <!-- Investment Packages Cards (1,20,000 & 5,50,000) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- 1,20,000 Package -->
                <div class="bg-white rounded-3xl p-6 border-2 border-slate-200/90 shadow-sm relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-wider block">National Growth Package</span>
                            <h3 class="text-2xl font-black text-slate-900 mt-0.5">৳১,২০,০০০/-</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-orange-100 text-orange-800 text-xs font-bold">Standard Pack</span>
                    </div>

                    <!-- Rule Breakdown Note -->
                    <div class="bg-amber-50 rounded-2xl p-3.5 border border-amber-200/80 text-xs text-amber-900 space-y-1 mb-4">
                        <div class="font-bold flex items-center gap-1">
                            <span>📌</span> ৳২০,০০০/- ডেভেলপমেন্ট চার্জ (মূল ইনভেস্ট নয়)
                        </div>
                        <div class="text-[11px] text-amber-800">
                            মূল ইনভেস্ট ধরা হয় <strong>৳১,০০,০০০/-</strong> যার উপর সাপ্তাহিক ও মাসিক রিটার্ন ক্যালকুলেট করা হয়।
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">সাপ্তাহিক রিটার্ন</span>
                            <span class="text-lg font-black text-slate-900">৳১,৭৫০/-</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">মাসিক রিটার্ন (আনুমানিক)</span>
                            <span class="text-lg font-black text-slate-900">৳৭,৫০০/-</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">মোট মেয়াদের রিটার্ন (১০০ সপ্তাহ)</span>
                            <span class="text-lg font-black text-emerald-600">৳১,৭৫,০০০/-</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">নেট প্রফিট</span>
                            <span class="text-lg font-black text-orange-600">৳৫৫,০০০/-</span>
                        </div>
                    </div>

                    <!-- Quantity Multiplier -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">প্যাকেজ সংখ্যা:</span>
                        <div class="flex items-center gap-2">
                            <button @click="calc120Count = Math.max(1, calc120Count - 1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold text-sm flex items-center justify-center">-</button>
                            <span class="w-8 text-center font-bold text-sm text-slate-900" x-text="calc120Count"></span>
                            <button @click="calc120Count++" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold text-sm flex items-center justify-center">+</button>
                        </div>
                    </div>
                    <div class="mt-3 bg-slate-900 text-white rounded-xl p-3 text-xs flex items-center justify-between">
                        <span>মোট মাসিক রিটার্ন (<span x-text="calc120Count"></span> টি):</span>
                        <span class="text-base font-black text-orange-400">৳<span x-text="(calc120Count * 7500).toLocaleString()"></span>/-</span>
                    </div>
                </div>

                <!-- 5,50,000 Package -->
                <div class="bg-white rounded-3xl p-6 border-2 border-orange-500/80 shadow-md relative overflow-hidden">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-wider block">International Growth Package</span>
                            <h3 class="text-2xl font-black text-slate-900 mt-0.5">৳৫,৫০,০০০/-</h3>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-orange-600 text-white text-xs font-bold">Premium Pack</span>
                    </div>

                    <!-- Rule Breakdown Note -->
                    <div class="bg-orange-50 rounded-2xl p-3.5 border border-orange-200/80 text-xs text-orange-900 space-y-1 mb-4">
                        <div class="font-bold flex items-center gap-1">
                            <span>📌</span> ৳৫০,০০০/- ডেভেলপমেন্ট চার্জ (মূল ইনভেস্ট নয়)
                        </div>
                        <div class="text-[11px] text-orange-800">
                            মূল ইনভেস্ট ধরা হয় <strong>৳৫,০০,০০০/-</strong> যার উপর সাপ্তাহিক ও মাসিক রিটার্ন ক্যালকুলেট করা হয়।
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">সাপ্তাহিক রিটার্ন</span>
                            <span class="text-lg font-black text-slate-900">৳১০,০০০/-</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">মাসিক রিটার্ন (আনুমানিক)</span>
                            <span class="text-lg font-black text-slate-900">৳৪২,৮৫৭/-</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">মোট মেয়াদের রিটার্ন (১০০ সপ্তাহ)</span>
                            <span class="text-lg font-black text-emerald-600">৳১০,০০,০০০/-</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="text-[11px] font-semibold text-slate-500 block">নেট প্রফিট</span>
                            <span class="text-lg font-black text-orange-600">৳৪,৫০,০০০/-</span>
                        </div>
                    </div>

                    <!-- Quantity Multiplier -->
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-700">প্যাকেজ সংখ্যা:</span>
                        <div class="flex items-center gap-2">
                            <button @click="calc550Count = Math.max(1, calc550Count - 1)" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold text-sm flex items-center justify-center">-</button>
                            <span class="w-8 text-center font-bold text-sm text-slate-900" x-text="calc550Count"></span>
                            <button @click="calc550Count++" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 font-bold text-sm flex items-center justify-center">+</button>
                        </div>
                    </div>
                    <div class="mt-3 bg-slate-900 text-white rounded-xl p-3 text-xs flex items-center justify-between">
                        <span>মোট মাসিক রিটার্ন (<span x-text="calc550Count"></span> টি):</span>
                        <span class="text-base font-black text-orange-400">৳<span x-text="(calc550Count * 42857).toLocaleString()"></span>/-</span>
                    </div>
                </div>
            </div>

            <!-- 10-Generation Commission Simulator -->
            <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <span>🚀</span> ১০ জেনারেশন রেফারেল ও মার্কেটিং কমিশন সিমুলেটর
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">SBL এফিলিয়েট ও টিম নেটওয়ার্ক লিডারশিপ আর্নিং ম্যাট্রিক্স</p>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 px-4 py-2 rounded-2xl text-right">
                        <span class="text-[10px] uppercase tracking-wider font-bold text-orange-800 block">মোট সম্ভাব্য কমিশন</span>
                        <span class="text-xl font-black text-orange-600">৳<span x-text="calcTotalCommission().toLocaleString()"></span>/-</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">জেনারেশন স্তর</th>
                                <th class="py-3 px-4">কমিশন রেট</th>
                                <th class="py-3 px-4">টিম ভলিউম / বিনিয়োগ</th>
                                <th class="py-3 px-4 text-right">মোট কমিশন আয়</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(gen, idx) in generations" :key="idx">
                                <tr class="hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-bold text-slate-900" x-text="gen.label"></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-md bg-orange-100 text-orange-800 font-black text-[11px]" x-text="gen.rate + '%'"></span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="flex items-center gap-1.5 max-w-[200px]">
                                            <span class="text-slate-400 font-bold">৳</span>
                                            <input type="number" x-model.number="gen.volume" step="10000" min="0" class="w-full text-xs font-bold rounded-lg border border-slate-200 px-2.5 py-1.5 focus:border-orange-500">
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-black text-slate-900" x-text="'৳' + Math.round(gen.volume * (gen.rate / 100)).toLocaleString() + '/-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- ============================================== -->
        <!-- TAB 5: PLANS & TOOLKIT RESOURCES               -->
        <!-- ============================================== -->
        <div x-show="currentTab === 'toolkit'" x-cloak class="space-y-6">
            
            <div class="border-b border-slate-200 pb-4">
                <h2 class="text-xl font-black text-slate-900 tracking-tight">SBL Playbook, Strategy & Counseling Toolkit</h2>
                <p class="text-xs text-slate-500">Official Field Playbook & 8-Figure Fast Track Blueprint</p>
            </div>

            <!-- Toolkit Subtabs -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs no-scrollbar">
                <button @click="toolkitSubTab = 'formula'" :class="toolkitSubTab === 'formula' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl font-bold flex-shrink-0 transition-all">
                    8-Figure Formula
                </button>
                <button @click="toolkitSubTab = 'counseling'" :class="toolkitSubTab === 'counseling' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl font-bold flex-shrink-0 transition-all">
                    10-Step Counseling
                </button>
                <button @click="toolkitSubTab = 'personality'" :class="toolkitSubTab === 'personality' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl font-bold flex-shrink-0 transition-all">
                    4-Color Personality
                </button>
                <button @click="toolkitSubTab = 'objections'" :class="toolkitSubTab === 'objections' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl font-bold flex-shrink-0 transition-all">
                    Objections Cheat Sheet
                </button>
                <button @click="toolkitSubTab = 'calendar'" :class="toolkitSubTab === 'calendar' ? 'bg-slate-900 text-white' : 'bg-white text-slate-700 border border-slate-200'" class="px-3 py-1.5 rounded-xl font-bold flex-shrink-0 transition-all">
                    30-Day Content Calendar
                </button>
            </div>

            <!-- Subtab 1: 8-Figure Formula -->
            <div x-show="toolkitSubTab === 'formula'" class="space-y-4">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="text-base font-black text-slate-900">🌟 The 8-Figure Daily Core Routine</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="p-4 rounded-2xl bg-orange-50/50 border border-orange-200/60">
                            <span class="text-xs font-bold text-orange-900 block mb-1">1. Prospecting (Morning)</span>
                            <p class="text-xs text-slate-600 leading-relaxed">Reach out to 5 new individuals every single day via WhatsApp, Facebook, or phone call. Add them directly into the Leads CRM.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-orange-50/50 border border-orange-200/60">
                            <span class="text-xs font-bold text-orange-900 block mb-1">2. Scheduled Presentations</span>
                            <p class="text-xs text-slate-600 leading-relaxed">Conduct at least 1 online (Meet/Zoom) or 1-to-1 offline presentation daily using the official SBL pitch deck.</p>
                        </div>
                        <div class="p-4 rounded-2xl bg-orange-50/50 border border-orange-200/60">
                            <span class="text-xs font-bold text-orange-900 block mb-1">3. 24-48h Follow-up Rule</span>
                            <p class="text-xs text-slate-600 leading-relaxed">Never leave a lead without an explicit scheduled next action. 80% of sales happen on the 5th to 12th touchpoint.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtab 2: Counseling Guide -->
            <div x-show="toolkitSubTab === 'counseling'" class="space-y-4">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-base font-black text-slate-900">🤝 10-Step Counseling Blueprint</h3>
                    <div class="space-y-2 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-2">
                            <span class="font-black text-orange-600">01.</span>
                            <span><strong>Building Rapport:</strong> Start with family, work, and aspirations. Listen 80%, speak 20%.</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-2">
                            <span class="font-black text-orange-600">02.</span>
                            <span><strong>Identifying Pain Points:</strong> Determine if their primary bottleneck is monthly income, job security, or extra time.</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-2">
                            <span class="font-black text-orange-600">03.</span>
                            <span><strong>Positioning SBL as the Bridge:</strong> Show how the 1,20,000 / 5,50,000 package solves their exact pain point.</span>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 flex items-start gap-2">
                            <span class="font-black text-orange-600">04.</span>
                            <span><strong>Closing Without Pressure:</strong> Ask: "Based on what you've seen, which package fits your current budget best?"</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtab 3: 4-Color Personality -->
            <div x-show="toolkitSubTab === 'personality'" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-2xl p-5 border-l-4 border-red-500 border border-slate-200/80 shadow-xs">
                        <span class="text-xs font-black text-red-600 uppercase tracking-wider block">🔴 RED (Dominant / Leader)</span>
                        <p class="text-xs text-slate-600 mt-1">Goal-oriented and decisive. Focus on <strong>bottom-line profit, speed, and market dominance</strong>. Don't waste time on small talk.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border-l-4 border-yellow-500 border border-slate-200/80 shadow-xs">
                        <span class="text-xs font-black text-yellow-600 uppercase tracking-wider block">🟡 YELLOW (Social / Influencer)</span>
                        <p class="text-xs text-slate-600 mt-1">Enthusiastic and people-driven. Highlight <strong>events, community, recognition, and fun</strong>.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border-l-4 border-blue-500 border border-slate-200/80 shadow-xs">
                        <span class="text-xs font-black text-blue-600 uppercase tracking-wider block">🔵 BLUE (Analytical / Detail-Oriented)</span>
                        <p class="text-xs text-slate-600 mt-1">Fact-driven and methodical. Show <strong>spreadsheets, company legal registration, exact weekly calculations, and ROI history</strong>.</p>
                    </div>
                    <div class="bg-white rounded-2xl p-5 border-l-4 border-emerald-500 border border-slate-200/80 shadow-xs">
                        <span class="text-xs font-black text-emerald-600 uppercase tracking-wider block">🟢 GREEN (Supportive / Steady)</span>
                        <p class="text-xs text-slate-600 mt-1">Cares about team support, security, and low risk. Emphasize <strong>step-by-step training, mentorship, and family security</strong>.</p>
                    </div>
                </div>
            </div>

            <!-- Subtab 4: Objections -->
            <div x-show="toolkitSubTab === 'objections'" class="space-y-4">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-4">
                    <h3 class="text-base font-black text-slate-900">🛡️ Top 3 Objections & Word-for-Word Rebuttals</h3>
                    <div class="space-y-3 text-xs">
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="font-bold text-rose-600 block mb-1">Objection 1: "টাকা নেই / I don't have enough money right now."</span>
                            <p class="text-slate-700 italic">"ভাই, আমি আপনার পরিস্থিতি পুরোপুরি বুঝতে পারছি। সত্যি বলতে, আজ যদি আপনার কাছে পর্যাপ্ত ব্যাকআপ ফান্ড থাকতো, তাহলে হয়তো নতুন ইনভেস্টের প্রয়োজন হতো না। আপনি কি চান আগামী ৫ বছর পরেও এই একই আর্থিক চাপ থাকুক, নাকি ১টি সঠিক সিদ্ধান্তে এটা পরিবর্তন করতে চান?"</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="font-bold text-rose-600 block mb-1">Objection 2: "আমি সময় দিতে পারব না / I am very busy."</span>
                            <p class="text-slate-700 italic">"দারুণ প্রশ্ন! এসবিএল বিজনেসের আসল সৌন্দর্যই হলো এতে ফুল-টাইম চাকরি বা ব্যবসা ছেড়ে দিতে হয় না। প্রতিদিন মাত্র ১ ঘণ্টা সঠিক সিস্টেমে কাজ করলেই সিস্টেম আপনার জন্য রেজাল্ট তৈরি করবে।"</p>
                        </div>
                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                            <span class="font-bold text-rose-600 block mb-1">Objection 3: "পরিবারের সাথে আলোচনা করে জানাবো / Need to ask family."</span>
                            <p class="text-slate-700 italic">"অবশ্যই, গুরুত্বপূর্ণ আর্থিক সিদ্ধান্তে পরিবারের পরামর্শ থাকা ভালো। তবে যেহেতু আপনি সরাসরি পুরো বিজনেস প্ল্যানটি দেখেছেন, তাই আমি প্রস্তাব করবো আপনার সাথে আপনার পরিবারকেও একটি ১৫ মিনিটের জুমে পুরো বিষয়টি বুঝিয়ে দেই।"</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtab 5: 30-Day Content Calendar -->
            <div x-show="toolkitSubTab === 'calendar'" class="space-y-4">
                <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs space-y-3">
                    <h3 class="text-base font-black text-slate-900">📅 30-Day Social Media Inbound Magnet</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 text-xs">
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-orange-600 block">Day 01 - Personal Story</span>
                            <p class="text-slate-600 mt-1">Why I decided to build a second source of income alongside my current work.</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-orange-600 block">Day 02 - Industry Truth</span>
                            <p class="text-slate-600 mt-1">Single source of income vs 3 streams: The 2026 inflation reality check.</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-orange-600 block">Day 03 - Team Milestone</span>
                            <p class="text-slate-600 mt-1">Welcoming our newest partner who reached their first milestone in 14 days.</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-orange-600 block">Day 04 - ROI Education</span>
                            <p class="text-slate-600 mt-1">How ৳১,২০,০০০ generates weekly passive returns without operational headache.</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-orange-600 block">Day 05 - Lifestyle Hook</span>
                            <p class="text-slate-600 mt-1">Working from a cafe with a laptop: The freedom of digital business ownership.</p>
                        </div>
                        <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                            <span class="font-bold text-orange-600 block">Day 06 - Q&A Direct CTA</span>
                            <p class="text-slate-600 mt-1">Dropping the 5 most asked questions about SBL partnership in the comments.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </main>

    <!-- Mobile Bottom Navigation Bar -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 z-40 bg-slate-950 border-t border-slate-800/80 shadow-2xl px-2 py-2 flex items-center justify-around">
        <button @click="setTab('dashboard')" :class="currentTab === 'dashboard' ? 'text-orange-500' : 'text-slate-400'" class="flex flex-col items-center gap-0.5 p-1 text-[10px] font-bold">
            <span class="text-base">🏠</span>
            <span>Dashboard</span>
        </button>
        <button @click="setTab('leads')" :class="currentTab === 'leads' ? 'text-orange-500' : 'text-slate-400'" class="flex flex-col items-center gap-0.5 p-1 text-[10px] font-bold">
            <span class="text-base">👥</span>
            <span>Leads</span>
        </button>
        <!-- Center Floating Action Button -->
        <button @click="openAddLeadModal()" class="w-12 h-12 -mt-5 rounded-2xl bg-orange-600 text-white flex items-center justify-center text-xl shadow-lg shadow-orange-600/40 border-2 border-slate-950 active:scale-95 transition-all">
            ⚡
        </button>
        <button @click="setTab('kanban')" :class="currentTab === 'kanban' ? 'text-orange-500' : 'text-slate-400'" class="flex flex-col items-center gap-0.5 p-1 text-[10px] font-bold">
            <span class="text-base">📋</span>
            <span>Kanban</span>
        </button>
        <button @click="setTab('calculator')" :class="currentTab === 'calculator' ? 'text-orange-500' : 'text-slate-400'" class="flex flex-col items-center gap-0.5 p-1 text-[10px] font-bold">
            <span class="text-base">🧮</span>
            <span>Calculator</span>
        </button>
    </nav>

    <!-- Add Lead Modal -->
    <div x-show="showAddLeadModal" 
         x-transition 
         class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-slate-950/70 backdrop-blur-xs" 
         x-cloak>
        <div @click.outside="showAddLeadModal = false" class="w-full max-w-lg bg-white rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 sm:p-6 border border-slate-200 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-slate-900">Add New Lead</h3>
                <button @click="showAddLeadModal = false" class="text-slate-400 hover:text-slate-700 text-xl font-bold w-8 h-8 rounded-full hover:bg-slate-100 flex items-center justify-center">&times;</button>
            </div>

            <form @submit.prevent="saveNewLead()" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Full Name *</label>
                        <input type="text" x-model="newLead.name" required placeholder="e.g. Rafiqul Islam" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mobile Number *</label>
                        <input type="tel" x-model="newLead.mobile" required inputmode="tel" placeholder="017xxxxxxxx" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">WhatsApp (Optional)</label>
                        <input type="tel" x-model="newLead.whatsapp" inputmode="tel" placeholder="01xxxxxxxxx" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Location / City</label>
                        <input type="text" x-model="newLead.location" placeholder="e.g. Dhaka, Mirpur" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Lead Source</label>
                        <select x-model="newLead.source" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-white">
                            <option value="Direct Call">Direct Call</option>
                            <option value="WhatsApp Inbound">WhatsApp Inbound</option>
                            <option value="Facebook Campaign">Facebook Campaign</option>
                            <option value="Referral">Referral</option>
                            <option value="Event/Seminar">Event / Seminar</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Initial Stage</label>
                        <select x-model="newLead.stage" class="w-full text-sm rounded-xl border border-slate-300 focus:border-orange-500 px-3 py-2 bg-white">
                            <template x-for="st in stages" :key="st.key">
                                <option :value="st.key" x-text="st.label"></option>
                            </template>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Quick Tag</label>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" @click="newLead.tag = newLead.tag === 'P1' ? '' : 'P1'" :class="newLead.tag === 'P1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold">P1: Product</button>
                        <button type="button" @click="newLead.tag = newLead.tag === 'E1' ? '' : 'E1'" :class="newLead.tag === 'E1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold">E1: E-commerce</button>
                        <button type="button" @click="newLead.tag = newLead.tag === 'A1' ? '' : 'A1'" :class="newLead.tag === 'A1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold">A1: Affiliate</button>
                        <button type="button" @click="newLead.tag = newLead.tag === 'I1' ? '' : 'I1'" :class="newLead.tag === 'I1' ? 'bg-orange-600 text-white' : 'bg-slate-100 text-slate-700'" class="px-2.5 py-1 rounded-lg text-xs font-bold">I1: Investment</button>
                    </div>
                </div>

                <div class="bg-orange-50 p-3.5 rounded-2xl border border-orange-200/80 space-y-2">
                    <span class="text-xs font-bold text-orange-900 block">⚡ Immediate Next Action</span>
                    <div class="grid grid-cols-2 gap-2">
                        <input type="text" x-model="newLead.next_action_type" placeholder="e.g. Follow-up Call" class="text-xs rounded-lg border border-slate-300 px-2.5 py-2 bg-white">
                        <input type="text" x-model="newLead.next_action_date" placeholder="e.g. Tomorrow 11 AM" class="text-xs rounded-lg border border-slate-300 px-2.5 py-2 bg-white">
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" @click="showAddLeadModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Cancel</button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white font-bold text-xs shadow-xs">Save Lead</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Application Script -->
    <script>
        function sblApp() {
            return {
                currentTab: 'dashboard',
                toolkitSubTab: 'formula',
                searchQuery: '',
                currentFilter: 'all',
                showAddLeadModal: false,
                calc120Count: 1,
                calc550Count: 1,
                toast: { show: false, message: '' },
                newLead: {
                    name: '',
                    mobile: '',
                    whatsapp: '',
                    location: '',
                    source: 'Direct Call',
                    stage: 'new',
                    tag: 'P1',
                    next_action_type: 'Follow-up Call',
                    next_action_date: 'Today 4:00 PM'
                },
                stages: [
                    { key: 'new', label: 'New Inquiry', dotColor: 'bg-blue-500' },
                    { key: 'contacted', label: 'Contacted', dotColor: 'bg-sky-500' },
                    { key: 'interested', label: 'Interested', dotColor: 'bg-amber-500' },
                    { key: 'qualified', label: 'Qualified', dotColor: 'bg-indigo-500' },
                    { key: 'presentation', label: 'Presentation', dotColor: 'bg-purple-500' },
                    { key: 'follow_up', label: 'Follow-up', dotColor: 'bg-orange-500' },
                    { key: 'decision', label: 'Decision', dotColor: 'bg-yellow-500' },
                    { key: 'converted', label: 'Converted', dotColor: 'bg-emerald-500' }
                ],
                generations: [
                    { label: '১ম জেনারেশন (ডাইরেক্ট স্পন্সর)', rate: 10, volume: 1000000 },
                    { label: '২য় জেনারেশন', rate: 2, volume: 2000000 },
                    { label: '৩য় জেনারেশন', rate: 1, volume: 3000000 },
                    { label: '৪র্থ জেনারেশন', rate: 1, volume: 4000000 },
                    { label: '৫ম জেনারেশন', rate: 0.5, volume: 5000000 },
                    { label: '৬ষ্ঠ জেনারেশন', rate: 0.1, volume: 6000000 },
                    { label: '৭ম জেনারেশন', rate: 0.1, volume: 7000000 },
                    { label: '৮ম জেনারেশন', rate: 0.1, volume: 8000000 },
                    { label: '৯ম জেনারেশন', rate: 0.1, volume: 9000000 },
                    { label: '১০ম জেনারেশন', rate: 0.1, volume: 10000000 }
                ],
                leads: [],
                initApp() {
                    const saved = localStorage.getItem('sbl_leads_data');
                    if (saved) {
                        try {
                            this.leads = JSON.parse(saved);
                        } catch (e) {
                            this.loadDefaultLeads();
                        }
                    } else {
                        this.loadDefaultLeads();
                    }

                    // Read hash on load
                    const hash = window.location.hash.replace('#', '');
                    if (['dashboard', 'leads', 'kanban', 'calculator', 'toolkit'].includes(hash)) {
                        this.currentTab = hash;
                    }

                    this.$nextTick(() => {
                        this.initSortable();
                    });
                },
                loadDefaultLeads() {
                    this.leads = [
                        { id: 1, name: 'Rafiqul Islam', mobile: '01711223344', whatsapp: '01711223344', location: 'Dhaka, Dhanmondi', source: 'Direct Call', stage: 'new', score: 85, temperature: 'Hot', tag: 'P1', next_action_type: 'Immediate Welcome Call', next_action_date: 'Today 2:00 PM', is_overdue: false },
                        { id: 2, name: 'Tanvir Hossain', mobile: '01822334455', whatsapp: '01822334455', location: 'Chattogram', source: 'Facebook Campaign', stage: 'follow_up', score: 78, temperature: 'Warm', tag: 'E1', next_action_type: 'Pricing Follow-up', next_action_date: 'Yesterday 5:00 PM', is_overdue: true },
                        { id: 3, name: 'Nasir Uddin', mobile: '01933445566', whatsapp: '01933445566', location: 'Sylhet, Sadar', source: 'Referral', stage: 'presentation', score: 92, temperature: 'Hot', tag: 'I1', next_action_type: 'Zoom Presentation', next_action_date: 'Today 7:00 PM', is_overdue: false },
                        { id: 4, name: 'Sharmin Sultana', mobile: '01644556677', whatsapp: '01644556677', location: 'Uttara, Dhaka', source: 'WhatsApp Inbound', stage: 'qualified', score: 80, temperature: 'Hot', tag: 'A1', next_action_type: 'Share SBL Plan Deck', next_action_date: 'Today 11:00 AM', is_overdue: true },
                        { id: 5, name: 'Kamrul Hasan', mobile: '01555667788', whatsapp: '01555667788', location: 'Rajshahi', source: 'Event/Seminar', stage: 'converted', score: 98, temperature: 'Hot', tag: 'I1', next_action_type: 'Onboarding Call', next_action_date: 'Done', is_overdue: false }
                    ];
                    this.saveLeads();
                },
                saveLeads() {
                    localStorage.setItem('sbl_leads_data', JSON.stringify(this.leads));
                },
                setTab(tab) {
                    this.currentTab = tab;
                    window.location.hash = tab;
                    if (tab === 'kanban') {
                        this.$nextTick(() => { this.initSortable(); });
                    }
                },
                filterStage(stageKey) {
                    this.setTab('leads');
                    this.currentFilter = 'all';
                    this.searchQuery = stageKey;
                },
                scrollKanbanTo(stageKey) {
                    const el = document.getElementById('col-' + stageKey);
                    if (el) el.scrollIntoView({ behavior: 'smooth', inline: 'center' });
                },
                cleanPhone(num) {
                    return (num || '').replace(/[^0-9]/g, '');
                },
                getStageLabel(key) {
                    const st = this.stages.find(s => s.key === key);
                    return st ? st.label : key;
                },
                getStageCount(key) {
                    return this.leads.filter(l => l.stage === key).length;
                },
                getLeadsInStage(key) {
                    return this.leads.filter(l => l.stage === key);
                },
                getDueTodayLeads() {
                    return this.leads.filter(l => (l.next_action_date || '').toLowerCase().includes('today'));
                },
                getOverdueLeads() {
                    return this.leads.filter(l => l.is_overdue);
                },
                getHotLeads() {
                    return this.leads.filter(l => l.score >= 80);
                },
                filteredLeads() {
                    let list = this.leads;
                    if (this.currentFilter === 'overdue') list = list.filter(l => l.is_overdue);
                    if (this.currentFilter === 'due_today') list = list.filter(l => (l.next_action_date || '').toLowerCase().includes('today'));
                    if (this.currentFilter === 'hot') list = list.filter(l => l.score >= 80);

                    if (this.searchQuery.trim()) {
                        const q = this.searchQuery.toLowerCase();
                        list = list.filter(l => 
                            l.name.toLowerCase().includes(q) || 
                            l.mobile.includes(q) || 
                            (l.location && l.location.toLowerCase().includes(q)) ||
                            l.stage.toLowerCase().includes(q)
                        );
                    }
                    return list;
                },
                openAddLeadModal() {
                    this.showAddLeadModal = true;
                },
                saveNewLead() {
                    const newId = Date.now();
                    this.leads.unshift({
                        id: newId,
                        name: this.newLead.name,
                        mobile: this.newLead.mobile,
                        whatsapp: this.newLead.whatsapp || this.newLead.mobile,
                        location: this.newLead.location,
                        source: this.newLead.source,
                        stage: this.newLead.stage,
                        score: 75,
                        temperature: 'Warm',
                        tag: this.newLead.tag,
                        next_action_type: this.newLead.next_action_type,
                        next_action_date: this.newLead.next_action_date,
                        is_overdue: false
                    });
                    this.saveLeads();
                    this.showAddLeadModal = false;
                    this.triggerToast('New lead added successfully!');
                    this.newLead.name = '';
                    this.newLead.mobile = '';
                    this.newLead.whatsapp = '';
                    this.newLead.location = '';
                },
                updateLeadStage(id, newStage) {
                    const lead = this.leads.find(l => l.id == id);
                    if (lead) {
                        lead.stage = newStage;
                        this.saveLeads();
                        this.triggerToast('Stage updated to ' + this.getStageLabel(newStage));
                    }
                },
                deleteLead(id) {
                    if (confirm('Are you sure you want to delete this lead?')) {
                        this.leads = this.leads.filter(l => l.id != id);
                        this.saveLeads();
                        this.triggerToast('Lead removed.');
                    }
                },
                calcTotalCommission() {
                    return this.generations.reduce((acc, curr) => acc + Math.round(curr.volume * (curr.rate / 100)), 0);
                },
                triggerToast(msg) {
                    this.toast.message = msg;
                    this.toast.show = true;
                    setTimeout(() => { this.toast.show = false; }, 3000);
                },
                initSortable() {
                    if (typeof Sortable === 'undefined') return;
                    document.querySelectorAll('.kanban-column').forEach(container => {
                        new Sortable(container, {
                            group: 'sbl-pipeline',
                            animation: 150,
                            ghostClass: 'opacity-40',
                            onEnd: (evt) => {
                                const leadId = evt.item.getAttribute('data-id');
                                const toStage = evt.to.getAttribute('data-stage');
                                const fromStage = evt.from.getAttribute('data-stage');
                                if (toStage === fromStage) return;

                                const lead = this.leads.find(l => l.id == leadId);
                                if (lead) {
                                    lead.stage = toStage;
                                    this.saveLeads();
                                    this.triggerToast('Pipeline updated: ' + lead.name + ' → ' + this.getStageLabel(toStage));
                                }
                            }
                        });
                    });
                }
            }
        }
    </script>
</body>
</html>\`;
}
`;

fs.writeFileSync(path.join(__dirname, 'worker.js'), workerCode, 'utf8');
console.log('worker.js generated successfully! Size: ' + fs.statSync(path.join(__dirname, 'worker.js')).size + ' bytes');
