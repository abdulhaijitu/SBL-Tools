@extends('layouts.app')

@section('page-title', 'Funnel & Performance Reports')
@section('page-subtitle', 'Lead Conversion Funnels & Source ROI Analysis')

@section('content')
<div class="space-y-6">

    <!-- Period Filter Tabs -->
    <div class="bg-white rounded-2xl p-4 border border-slate-200/80 shadow-xs flex items-center justify-between">
        <div class="flex items-center gap-2 overflow-x-auto text-xs">
            <span class="text-slate-400 font-semibold uppercase tracking-wider text-[11px] mr-1">Period:</span>
            <a href="{{ route('reports.index', ['period' => 'today']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $period === 'today' ? 'bg-orange-600 text-white border-orange-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                Today
            </a>
            <a href="{{ route('reports.index', ['period' => 'week']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $period === 'week' ? 'bg-orange-600 text-white border-orange-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                This Week
            </a>
            <a href="{{ route('reports.index', ['period' => 'month']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $period === 'month' ? 'bg-orange-600 text-white border-orange-600' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                This Month
            </a>
            <a href="{{ route('reports.index', ['period' => 'all']) }}" class="px-3 py-1.5 rounded-lg border font-medium {{ $period === 'all' ? 'bg-slate-900 text-white border-slate-900' : 'bg-slate-50 text-slate-600 border-slate-200 hover:bg-slate-100' }}">
                All Time
            </a>
        </div>
    </div>

    <!-- Conversion Highlight Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Leads</span>
            <span class="text-3xl font-bold text-slate-900 mt-1 block">{{ $totalLeads }}</span>
            <span class="text-xs text-slate-400">Total pipeline acquisition</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">Converted Leads</span>
            <span class="text-3xl font-bold text-emerald-700 mt-1 block">{{ $convertedLeads }}</span>
            <span class="text-xs text-slate-400">Customers & Members</span>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-slate-200/80 shadow-xs">
            <span class="text-xs font-semibold text-orange-600 uppercase tracking-wider block">Overall Conversion Rate</span>
            <span class="text-3xl font-bold text-orange-600 mt-1 block">{{ $conversionRate }}%</span>
            <span class="text-xs text-slate-400">Leads into converted deals</span>
        </div>
    </div>

    <!-- Funnel Breakdown (Section 22) -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-4">Lead Funnel Distribution</h3>
        <div class="space-y-3">
            @foreach ($funnelData as $stageKey => $data)
                <div>
                    <div class="flex items-center justify-between text-xs mb-1">
                        <span class="font-bold text-slate-800">{{ $data['label'] }}</span>
                        <span class="font-semibold text-slate-600">{{ $data['count'] }} leads ({{ $data['percentage'] }}%)</span>
                    </div>
                    <div class="w-full h-3 bg-slate-100 rounded-full overflow-hidden">
                        <div class="h-full bg-orange-500 rounded-full transition-all duration-500" style="width: {{ $data['percentage'] }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Lead Source Performance (Section 15 & 22) -->
    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-base font-bold text-slate-900">Lead Source Effectiveness</h3>
            <p class="text-xs text-slate-500">Track which marketing sources produce real presentations and conversions.</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-slate-600">
                <thead class="bg-slate-50 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-200">
                    <tr>
                        <th class="py-3 px-4">Lead Source</th>
                        <th class="py-3 px-4 text-center">Total Leads</th>
                        <th class="py-3 px-4 text-center">Presentations</th>
                        <th class="py-3 px-4 text-center">Converted</th>
                        <th class="py-3 px-4 text-right">Conversion Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($sources as $source)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $source['name'] }}</td>
                            <td class="py-3 px-4 text-center font-semibold text-slate-800">{{ $source['total_leads'] }}</td>
                            <td class="py-3 px-4 text-center text-purple-700 font-semibold">{{ $source['presentations'] }}</td>
                            <td class="py-3 px-4 text-center text-emerald-700 font-bold">{{ $source['converted'] }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="px-2.5 py-1 rounded-full font-bold {{ $source['conversion_rate'] > 0 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $source['conversion_rate'] }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">No sources found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Activity Metrics -->
    <div class="bg-white rounded-2xl p-6 border border-slate-200/80 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-4">Activity Throughput</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-500 uppercase font-semibold block">Total Logged Activities</span>
                <span class="text-2xl font-bold text-slate-900 mt-1 block">{{ $activityStats['total_activities'] }}</span>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-500 uppercase font-semibold block">Calls Made</span>
                <span class="text-2xl font-bold text-orange-600 mt-1 block">{{ $activityStats['calls'] }}</span>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-500 uppercase font-semibold block">Presentations</span>
                <span class="text-2xl font-bold text-purple-600 mt-1 block">{{ $activityStats['presentations'] }}</span>
            </div>
            <div class="p-4 bg-slate-50 rounded-xl border border-slate-100">
                <span class="text-xs text-slate-500 uppercase font-semibold block">Completed Tasks</span>
                <span class="text-2xl font-bold text-emerald-600 mt-1 block">{{ $activityStats['completed_tasks'] }}</span>
            </div>
        </div>
    </div>

</div>
@endsection

