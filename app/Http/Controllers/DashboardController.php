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
        $user = auth()->user();

        // Scope queries based on user role & permissions
        $leadScope = Lead::query();
        $taskScope = Task::query();

        if ($user && ! $user->hasRole('Super Admin') && ! $user->can('leads.manage') && ! $user->hasRole('Sales Manager')) {
            $leadScope->where('owner_user_id', $user->id);
            $taskScope->where('user_id', $user->id);
        }

        // 1. Today's Actions
        $todayTasks = (clone $taskScope)->with('lead')
            ->where('status', '!=', TaskStatus::COMPLETED->value)
            ->where('status', '!=', TaskStatus::CANCELLED->value)
            ->whereDate('due_at', $today)
            ->orderBy('priority', 'desc')
            ->orderBy('due_at', 'asc')
            ->get();

        $followupsDueToday = (clone $leadScope)->with('source')->dueToday()->get();

        $overdueFollowups = (clone $leadScope)->with('source')
            ->overdueFollowups()
            ->orderBy('next_action_at', 'asc')
            ->get();

        $presentationsToday = Presentation::with('lead')
            ->whereDate('date_time', $today)
            ->orderBy('date_time', 'asc')
            ->get();

        $presentationsThisMonthCount = Presentation::whereMonth('date_time', $today->month)
            ->whereYear('date_time', $today->year)
            ->count();

        $newLeadsTodayCount = (clone $leadScope)->whereDate('created_at', $today)->count();

        // 2. Needs Attention Priority Aggregate
        // Order: 1. Overdue urgency (time past), 2. Hot lead, 3. High score, 4. Due today
        $attentionList = collect();

        // Add overdue follow-ups
        foreach ($overdueFollowups as $lead) {
            $diffHours = $lead->next_action_at ? max(1, (int) $lead->next_action_at->diffInHours(now())) : 24;
            $attentionList->push([
                'id' => 'lead-' . $lead->id,
                'lead' => $lead,
                'task' => null,
                'type' => 'followup',
                'title' => $lead->next_action_type ?: 'Follow-up Call',
                'urgency' => 'overdue',
                'urgency_order' => 1,
                'urgency_score' => $diffHours,
                'badge_text' => 'Overdue ' . ($lead->next_action_at?->diffForHumans() ?? ''),
                'score' => $lead->score ?? 0,
                'is_hot' => $lead->temperature === LeadTemperature::HOT,
                'due_time' => $lead->next_action_at,
            ]);
        }

        // Add today follow-ups
        foreach ($followupsDueToday as $lead) {
            $attentionList->push([
                'id' => 'lead-' . $lead->id,
                'lead' => $lead,
                'task' => null,
                'type' => 'followup',
                'title' => $lead->next_action_type ?: 'Scheduled Follow-up',
                'urgency' => 'today',
                'urgency_order' => 2,
                'urgency_score' => 0,
                'badge_text' => 'Due Today',
                'score' => $lead->score ?? 0,
                'is_hot' => $lead->temperature === LeadTemperature::HOT,
                'due_time' => $lead->next_action_at,
            ]);
        }

        // Add high-priority tasks due today
        foreach ($todayTasks->where('priority', 'high') as $task) {
            if ($task->lead) {
                $attentionList->push([
                    'id' => 'task-' . $task->id,
                    'lead' => $task->lead,
                    'task' => $task,
                    'type' => 'task',
                    'title' => $task->title,
                    'urgency' => 'task_urgent',
                    'urgency_order' => 3,
                    'urgency_score' => 0,
                    'badge_text' => 'Urgent Task',
                    'score' => $task->lead->score ?? 0,
                    'is_hot' => $task->lead->temperature === LeadTemperature::HOT,
                    'due_time' => $task->due_at,
                ]);
            }
        }

        // Deduplicate attention items visually by lead mobile or name, keeping the most urgent
        $uniqueAttentionItems = $attentionList
            ->sortBy(function ($item) {
                return [
                    $item['urgency_order'],
                    $item['is_hot'] ? 0 : 1,
                    -$item['urgency_score'],
                    -$item['score'],
                ];
            })
            ->unique(function ($item) {
                return $item['lead']->mobile ?: $item['lead']->name;
            })
            ->values();

        $needsAttention = $uniqueAttentionItems->take(5);
        $totalAttentionCount = $uniqueAttentionItems->count();

        // 3. Funnel Summary
        $stageCounts = (clone $leadScope)->selectRaw('stage, count(*) as count')
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
        $totalActiveLeads = (clone $leadScope)->activePipeline()->count();
        $totalPresentations = Presentation::count();

        // 4. Hot Leads (Deduplicated visually by phone)
        $hotLeadsRaw = (clone $leadScope)->with('source')
            ->activePipeline()
            ->where('temperature', LeadTemperature::HOT->value)
            ->orderBy('score', 'desc')
            ->get();

        $hotLeads = $hotLeadsRaw
            ->unique(fn($l) => $l->mobile ?: $l->name)
            ->take(5)
            ->values();

        // 5. Needs Re-engagement (Stale Leads - Inactive > 7 days, deduplicated visually)
        $staleLeadsRaw = (clone $leadScope)->with('source')
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
            ->orderBy('score', 'desc')
            ->get();

        $staleLeads = $staleLeadsRaw
            ->unique(fn($l) => $l->mobile ?: $l->name)
            ->take(5)
            ->values();

        // 6. Recent Activities
        $recentActivities = Activity::with(['lead', 'user'])
            ->orderBy('performed_at', 'desc')
            ->limit(6)
            ->get();

        // 7. Team Explorer Summary for current user
        $teamRoot = null;
        $directTeamSummary = [
            'total_direct' => 0,
            'left_count' => 0,
            'right_count' => 0,
            'left_bv' => 0,
            'right_bv' => 0,
            'suggested_placement' => 'LEFT',
        ];

        if ($user) {
            $teamRoot = \App\Models\BinaryNode::with('children')
                ->where('tree_owner_id', $user->id)
                ->whereNull('parent_id')
                ->first();

            if ($teamRoot) {
                $placedChildren = $teamRoot->children->where('is_target', false);
                $leftCount = $placedChildren->where('branch', 'LEFT')->count();
                $rightCount = $placedChildren->where('branch', 'RIGHT')->count();
                $totalDirect = $leftCount + $rightCount;

                $suggested = $leftCount <= $rightCount ? 'LEFT' : 'RIGHT';

                $directTeamSummary = [
                    'total_direct' => $totalDirect,
                    'left_count' => $leftCount,
                    'right_count' => $rightCount,
                    'left_bv' => (float) ($teamRoot->left_bv ?? 0),
                    'right_bv' => (float) ($teamRoot->right_bv ?? 0),
                    'suggested_placement' => $suggested,
                ];
            }
        }

        return view('dashboard', compact(
            'todayTasks',
            'followupsDueToday',
            'overdueFollowups',
            'presentationsToday',
            'presentationsThisMonthCount',
            'newLeadsTodayCount',
            'needsAttention',
            'totalAttentionCount',
            'funnelStages',
            'totalLeads',
            'totalActiveLeads',
            'totalPresentations',
            'hotLeads',
            'staleLeads',
            'recentActivities',
            'teamRoot',
            'directTeamSummary'
        ));
    }
}
