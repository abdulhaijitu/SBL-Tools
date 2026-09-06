<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadSource;
use App\Models\Presentation;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->query('period', 'all'); // 'today', 'week', 'month', 'all'

        $leadQuery = Lead::query();
        $activityQuery = Activity::query();
        $taskQuery = Task::query();

        if ($period === 'today') {
            $leadQuery->whereDate('created_at', Carbon::today());
            $activityQuery->whereDate('performed_at', Carbon::today());
            $taskQuery->whereDate('created_at', Carbon::today());
        } elseif ($period === 'week') {
            $leadQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $activityQuery->whereBetween('performed_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            $taskQuery->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $leadQuery->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            $activityQuery->whereBetween('performed_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
            $taskQuery->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
        }

        $totalLeads = $leadQuery->count();
        $convertedLeads = (clone $leadQuery)->where('stage', LeadStage::CONVERTED->value)->count();
        $conversionRate = $totalLeads > 0 ? round(($convertedLeads / $totalLeads) * 100, 1) : 0;

        // Funnel Stages Breakdown
        $stages = LeadStage::cases();
        $funnelData = [];
        foreach ($stages as $stage) {
            $count = (clone $leadQuery)->where('stage', $stage->value)->count();
            $funnelData[$stage->value] = [
                'label' => $stage->label(),
                'count' => $count,
                'percentage' => $totalLeads > 0 ? round(($count / $totalLeads) * 100, 1) : 0,
                'badge' => $stage->badgeClasses(),
            ];
        }

        // Lead Sources Performance
        $sources = LeadSource::withCount(['leads'])->get()->map(function ($source) {
            $leadsCount = $source->leads_count;
            $converted = $source->leads()->where('stage', LeadStage::CONVERTED->value)->count();
            $presentations = $source->leads()->where('stage', LeadStage::PRESENTATION->value)->count();
            $rate = $leadsCount > 0 ? round(($converted / $leadsCount) * 100, 1) : 0;

            return [
                'name' => $source->name,
                'total_leads' => $leadsCount,
                'presentations' => $presentations,
                'converted' => $converted,
                'conversion_rate' => $rate,
            ];
        })->sortByDesc('total_leads');

        // Activities Summary
        $activityStats = [
            'total_activities' => $activityQuery->count(),
            'calls' => (clone $activityQuery)->where('type', 'call')->count(),
            'meetings' => (clone $activityQuery)->where('type', 'meeting')->count(),
            'presentations' => Presentation::count(),
            'completed_tasks' => Task::where('status', TaskStatus::COMPLETED->value)->count(),
        ];

        return view('reports.index', compact('totalLeads', 'convertedLeads', 'conversionRate', 'funnelData', 'sources', 'activityStats', 'period'));
    }
}

