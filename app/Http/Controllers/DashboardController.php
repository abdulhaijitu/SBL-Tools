<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\LeadTemperature;
use App\Enums\TaskStatus;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Presentation;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today();

        // 1. Today's Actions
        $todayTasks = Task::with('lead')
            ->where('status', '!=', TaskStatus::COMPLETED->value)
            ->where('status', '!=', TaskStatus::CANCELLED->value)
            ->whereDate('due_at', $today)
            ->orderBy('priority', 'desc')
            ->get();

        $followupsDueToday = Lead::with('source')->dueToday()->get();

        $overdueFollowups = Lead::with('source')
            ->overdueFollowups()
            ->orderBy('next_action_at', 'asc')
            ->limit(10)
            ->get();

        $presentationsToday = Presentation::with('lead')
            ->whereDate('date_time', $today)
            ->orderBy('date_time', 'asc')
            ->get();

        $newLeadsTodayCount = Lead::whereDate('created_at', $today)->count();

        // 2. Optimized Funnel Summary (Single Grouped Query instead of 8 queries)
        $stageCounts = Lead::selectRaw('stage, count(*) as count')
            ->groupBy('stage')
            ->pluck('count', 'stage')
            ->all();

        $funnelStages = [
            LeadStage::NEW->value => $stageCounts[LeadStage::NEW->value] ?? 0,
            LeadStage::CONTACTED->value => $stageCounts[LeadStage::CONTACTED->value] ?? 0,
            LeadStage::INTERESTED->value => $stageCounts[LeadStage::INTERESTED->value] ?? 0,
            LeadStage::QUALIFIED->value => $stageCounts[LeadStage::QUALIFIED->value] ?? 0,
            LeadStage::PRESENTATION->value => $stageCounts[LeadStage::PRESENTATION->value] ?? 0,
            LeadStage::FOLLOW_UP->value => $stageCounts[LeadStage::FOLLOW_UP->value] ?? 0,
            LeadStage::DECISION->value => $stageCounts[LeadStage::DECISION->value] ?? 0,
            LeadStage::CONVERTED->value => $stageCounts[LeadStage::CONVERTED->value] ?? 0,
        ];

        $totalLeads = array_sum($stageCounts);
        $totalActiveLeads = Lead::activePipeline()->count();
        $totalPresentations = Presentation::count();

        // 3. Lead Priority with eager loading
        $hotLeads = Lead::with('source')
            ->activePipeline()
            ->where('temperature', LeadTemperature::HOT->value)
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get();

        $warmLeads = Lead::with('source')
            ->activePipeline()
            ->where('temperature', LeadTemperature::WARM->value)
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get();

        $staleLeads = Lead::with('source')
            ->activePipeline()
            ->where(function ($q) {
                $q->where('temperature', LeadTemperature::STALE->value)
                    ->orWhere(function ($sub) {
                        $sub->whereIn('temperature', [LeadTemperature::HOT->value, LeadTemperature::WARM->value])
                            ->where(function ($dateQ) {
                                $dateQ->where('last_contact_at', '<=', now()->subDays(7))
                                    ->orWhere(function ($nullQ) {
                                        $nullQ->whereNull('last_contact_at')
                                            ->where('created_at', '<=', now()->subDays(7));
                                    });
                            });
                    });
            })
            ->limit(5)
            ->get();

        // 4. Recent Activities
        $recentActivities = Activity::with(['lead', 'user'])
            ->orderBy('performed_at', 'desc')
            ->limit(8)
            ->get();

        // 5. Team Explorer Summary for current user
        $currentUser = auth()->user();
        $teamRoot = null;
        if ($currentUser) {
            $teamRoot = \App\Models\BinaryNode::with('children')
                ->where('tree_owner_id', $currentUser->id)
                ->whereNull('parent_id')
                ->first();
        }

        return view('dashboard', compact(
            'todayTasks',
            'followupsDueToday',
            'overdueFollowups',
            'presentationsToday',
            'newLeadsTodayCount',
            'funnelStages',
            'totalLeads',
            'totalActiveLeads',
            'totalPresentations',
            'hotLeads',
            'warmLeads',
            'staleLeads',
            'recentActivities',
            'teamRoot'
        ));
    }
}
