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

        $followupsDueToday = Lead::dueToday()->get();

        $overdueFollowups = Lead::overdueFollowups()
            ->orderBy('next_action_at', 'asc')
            ->limit(10)
            ->get();

        $presentationsToday = Presentation::with('lead')
            ->whereDate('date_time', $today)
            ->orderBy('date_time', 'asc')
            ->get();

        $newLeadsTodayCount = Lead::whereDate('created_at', $today)->count();

        // 2. Funnel Summary
        $funnelStages = [
            LeadStage::NEW->value => Lead::where('stage', LeadStage::NEW->value)->count(),
            LeadStage::CONTACTED->value => Lead::where('stage', LeadStage::CONTACTED->value)->count(),
            LeadStage::INTERESTED->value => Lead::where('stage', LeadStage::INTERESTED->value)->count(),
            LeadStage::QUALIFIED->value => Lead::where('stage', LeadStage::QUALIFIED->value)->count(),
            LeadStage::PRESENTATION->value => Lead::where('stage', LeadStage::PRESENTATION->value)->count(),
            LeadStage::FOLLOW_UP->value => Lead::where('stage', LeadStage::FOLLOW_UP->value)->count(),
            LeadStage::DECISION->value => Lead::where('stage', LeadStage::DECISION->value)->count(),
            LeadStage::CONVERTED->value => Lead::where('stage', LeadStage::CONVERTED->value)->count(),
        ];

        $totalLeads = Lead::count();

        // 3. Lead Priority
        $hotLeads = Lead::activePipeline()
            ->where('temperature', LeadTemperature::HOT->value)
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get();

        $warmLeads = Lead::activePipeline()
            ->where('temperature', LeadTemperature::WARM->value)
            ->orderBy('score', 'desc')
            ->limit(5)
            ->get();

        $staleLeads = Lead::activePipeline()
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

        return view('dashboard', compact(
            'todayTasks',
            'followupsDueToday',
            'overdueFollowups',
            'presentationsToday',
            'newLeadsTodayCount',
            'funnelStages',
            'totalLeads',
            'hotLeads',
            'warmLeads',
            'staleLeads',
            'recentActivities'
        ));
    }
}

