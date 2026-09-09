<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\LeadTemperature;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\LeadInterest;
use App\Models\LeadSource;
use App\Models\Presentation;
use App\Models\SblContact;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeadController extends Controller
{
    /**
     * Display listing: Table or Kanban view
     */
    public function index(Request $request): View
    {
        if ($request->routeIs('members.index')) {
            $request->merge(['stage' => LeadStage::CONVERTED->value, 'view' => 'table']);
        }
        $viewMode = $request->query('view', 'table'); // 'table' or 'kanban'

        $query = Lead::with(['source', 'owner', 'interests']);

        // Search
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('whatsapp', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($stage = $request->input('stage')) {
            $query->where('stage', $stage);
        }

        if ($temperature = $request->input('temperature')) {
            $query->where('temperature', $temperature);
        }

        if ($sourceId = $request->input('source_id')) {
            $query->where('lead_source_id', $sourceId);
        }

        if ($filter = $request->input('filter')) {
            if ($filter === 'overdue') {
                $query->overdueFollowups();
            } elseif ($filter === 'due_today') {
                $query->dueToday();
            } elseif ($filter === 'needs_action') {
                $query->needsNextAction();
            }
        }

        $sources = LeadSource::where('is_active', true)->orderBy('order')->get();
        if ($sources->isEmpty()) {
            $sources = LeadSource::orderBy('id')->get();
        }
        $stages = LeadStage::cases();
        $temperatures = LeadTemperature::cases();

        if ($viewMode === 'kanban') {
            $allLeads = $query->orderBy('score', 'desc')->get();
            $pipelineStages = LeadStage::activePipelineStages();

            $kanbanColumns = [];
            foreach ($pipelineStages as $stageEnum) {
                $kanbanColumns[$stageEnum->value] = $allLeads->where('stage', $stageEnum);
            }

            return view('leads.kanban', compact('kanbanColumns', 'sources', 'stages', 'temperatures', 'viewMode'));
        }

        $leads = $query->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();

        return view('leads.index', compact('leads', 'sources', 'stages', 'temperatures', 'viewMode'));
    }

    /**
     * Show create form (fast entry <30s)
     */
    public function create(): View
    {
        $sources = LeadSource::where('is_active', true)->orderBy('order')->get();
        if ($sources->isEmpty()) {
            $sources = LeadSource::orderBy('id')->get();
        }
        $stages = LeadStage::cases();
        $sblContacts = SblContact::orderBy('sort_order')->orderBy('department')->get();
        $teamMembers = User::whereNotNull('phone')->where('phone', '!=', '')->orderBy('name')->get();

        return view('leads.create', compact('sources', 'stages', 'sblContacts', 'teamMembers'));
    }

    /**
     * Store newly created lead
     */
    public function store(Request $request): RedirectResponse
    {
        // Sanitize facebook_url if given without scheme
        if ($request->filled('facebook_url') && !preg_match('#^https?://#i', $request->input('facebook_url'))) {
            $request->merge(['facebook_url' => 'https://' . ltrim($request->input('facebook_url'), '/')]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|string',
            'facebook_url' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'profession_or_business' => 'nullable|string|max:255',
            'lead_source_id' => 'required|integer',
            'lead_source_detail' => 'nullable|string|max:255',
            'interest_types' => 'nullable|array',
            'lead_tag' => 'nullable|string|max:20',
            'stage' => 'nullable|string',
            'budget_range' => 'nullable|string|max:100',
            'decision_timeline' => 'nullable|string|max:100',
            'next_action_type' => 'nullable|string|max:100',
            'next_action_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        DB::transaction(function () use ($validated, $request, &$lead) {
            $stage = ! empty($validated['stage']) ? (LeadStage::tryFrom($validated['stage']) ?? LeadStage::NEW) : LeadStage::NEW;

            $lead = new Lead();
            $lead->fill($validated);
            $lead->stage = $stage;
            $lead->owner_user_id = Auth::id() ?: 1;
            $lead->interest_types = $validated['interest_types'] ?? [];
            $lead->last_contact_at = now();

            // Auto calculate initial score and temperature
            $lead->calculateScoreAndTemperature();
            $lead->save();

            // Save interests pivot
            if (! empty($validated['interest_types'])) {
                foreach ($validated['interest_types'] as $interest) {
                    LeadInterest::create([
                        'lead_id' => $lead->id,
                        'interest' => $interest,
                    ]);
                }
            }

            // Create timeline activity
            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id() ?: 1,
                'type' => 'lead_created',
                'title' => 'New Lead Added',
                'description' => "Initial Stage: {$lead->stage->label()}, Source: " . ($lead->source->name ?? 'Direct'),
                'performed_at' => now(),
            ]);

            // If next action is scheduled, create corresponding Task
            if (! empty($validated['next_action_at'])) {
                Task::create([
                    'title' => ($validated['next_action_type'] ?? 'Follow-up') . ' with ' . $lead->name,
                    'type' => TaskType::FOLLOW_UP,
                    'related_lead_id' => $lead->id,
                    'user_id' => Auth::id() ?: 1,
                    'due_at' => $validated['next_action_at'],
                    'priority' => TaskPriority::HIGH,
                    'status' => TaskStatus::PENDING,
                    'notes' => $validated['notes'] ?? null,
                ]);
            }
        });

        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Lead created successfully!');
    }

    /**
     * Display lead profile & chronological timeline
     */
    public function show(Lead $lead): View
    {
        $lead->load(['source', 'owner', 'interests', 'activities.user', 'tasks', 'presentations']);
        $sources = LeadSource::where('is_active', true)->orderBy('order')->get();
        if ($sources->isEmpty()) {
            $sources = LeadSource::orderBy('id')->get();
        }
        $stages = LeadStage::cases();

        return view('leads.show', compact('lead', 'sources', 'stages'));
    }

    /**
     * Show edit form
     */
    public function edit(Lead $lead): View
    {
        $sources = LeadSource::where('is_active', true)->orderBy('order')->get();
        if ($sources->isEmpty()) {
            $sources = LeadSource::orderBy('id')->get();
        }
        $stages = LeadStage::cases();
        $sblContacts = SblContact::orderBy('sort_order')->orderBy('department')->get();
        $teamMembers = User::whereNotNull('phone')->where('phone', '!=', '')->orderBy('name')->get();

        return view('leads.edit', compact('lead', 'sources', 'stages', 'sblContacts', 'teamMembers'));
    }

    /**
     * Update lead details
     */
    public function update(Request $request, Lead $lead): RedirectResponse
    {
        // Sanitize facebook_url if given without scheme
        if ($request->filled('facebook_url') && !preg_match('#^https?://#i', $request->input('facebook_url'))) {
            $request->merge(['facebook_url' => 'https://' . ltrim($request->input('facebook_url'), '/')]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:30',
            'whatsapp' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:255',
            'photo' => 'nullable|string',
            'facebook_url' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'profession_or_business' => 'nullable|string|max:255',
            'lead_source_id' => 'required|integer',
            'lead_source_detail' => 'nullable|string|max:255',
            'interest_types' => 'nullable|array',
            'lead_tag' => 'nullable|string|max:20',
            'stage' => 'required|string',
            'score' => 'nullable|integer|min:0|max:100',
            'is_manual_score' => 'nullable|boolean',
            'budget_range' => 'nullable|string|max:100',
            'decision_timeline' => 'nullable|string|max:100',
            'next_action_type' => 'nullable|string|max:100',
            'next_action_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $oldStage = $lead->stage;
        $newStage = LeadStage::tryFrom($validated['stage']) ?? $lead->stage;

        $lead->fill($validated);
        $lead->stage = $newStage;
        $lead->is_manual_score = $request->has('is_manual_score');

        if (! $lead->is_manual_score) {
            $lead->calculateScoreAndTemperature();
        } else {
            $lead->score = $validated['score'] ?? $lead->score;
            if ($lead->score >= 80) {
                $lead->temperature = LeadTemperature::HOT;
            } elseif ($lead->score >= 50) {
                $lead->temperature = LeadTemperature::WARM;
            } else {
                $lead->temperature = LeadTemperature::COLD;
            }
        }

        $lead->save();

        // Sync interests
        $lead->interests()->delete();
        if (! empty($validated['interest_types'])) {
            foreach ($validated['interest_types'] as $interest) {
                LeadInterest::create([
                    'lead_id' => $lead->id,
                    'interest' => $interest,
                ]);
            }
        }

        // Record activity if stage changed
        if ($oldStage !== $newStage) {
            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id() ?? 1,
                'type' => 'stage_change',
                'title' => "Stage changed to {$newStage->label()}",
                'description' => "Changed from {$oldStage->label()} to {$newStage->label()}",
                'metadata' => [
                    'old_stage' => $oldStage->value,
                    'new_stage' => $newStage->value,
                ],
                'performed_at' => now(),
            ]);
        }

        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Lead updated successfully!');
    }

    /**
     * AJAX/Direct update stage (Kanban drag & drop)
     */
    public function updateStage(Request $request, Lead $lead): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'stage' => 'required|string',
        ]);

        $oldStage = $lead->stage;
        $newStage = LeadStage::from($validated['stage']);

        if ($oldStage !== $newStage) {
            $lead->stage = $newStage;
            $lead->last_contact_at = now();

            if ($newStage === LeadStage::CONVERTED) {
                $lead->converted_at = now();
            }

            $lead->calculateScoreAndTemperature();
            $lead->save();

            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id() ?? 1,
                'type' => 'stage_change',
                'title' => "Stage moved to {$newStage->label()}",
                'description' => "Moved from {$oldStage->label()} to {$newStage->label()}",
                'metadata' => [
                    'old_stage' => $oldStage->value,
                    'new_stage' => $newStage->value,
                ],
                'performed_at' => now(),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'stage' => $lead->stage->value,
                'stage_label' => $lead->stage->label(),
                'temperature' => $lead->temperature->value,
                'score' => $lead->score,
            ]);
        }

        return back()->with('success', "Stage updated to {$newStage->label()}");
    }

    /**
     * Add quick interaction activity (Call, WhatsApp, Note, etc.)
     */
    public function addActivity(Request $request, Lead $lead): RedirectResponse
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'next_action_type' => 'nullable|string|max:100',
            'next_action_at' => 'nullable|date',
        ]);

        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id() ?? 1,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'performed_at' => now(),
        ]);

        $lead->last_contact_at = now();

        if (! empty($validated['next_action_at'])) {
            $lead->next_action_type = $validated['next_action_type'] ?? 'Follow-up';
            $lead->next_action_at = $validated['next_action_at'];

            // Create or schedule task
            Task::create([
                'title' => ($validated['next_action_type'] ?? 'Follow-up') . ' with ' . $lead->name,
                'type' => TaskType::FOLLOW_UP,
                'related_lead_id' => $lead->id,
                'user_id' => Auth::id() ?? 1,
                'due_at' => $validated['next_action_at'],
                'priority' => TaskPriority::HIGH,
                'status' => TaskStatus::PENDING,
                'notes' => $validated['description'] ?? null,
            ]);
        }

        $lead->calculateScoreAndTemperature();
        $lead->save();

        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Activity recorded successfully!');
    }

    /**
     * Convert lead
     */
    public function convert(Lead $lead): RedirectResponse
    {
        $oldStage = $lead->stage;
        $lead->stage = LeadStage::CONVERTED;
        $lead->converted_at = now();
        $lead->last_contact_at = now();
        $lead->score = 100;
        $lead->temperature = LeadTemperature::HOT;
        $lead->save();

        Activity::create([
            'lead_id' => $lead->id,
            'user_id' => Auth::id() ?? 1,
            'type' => 'conversion',
            'title' => 'Lead Converted!',
            'description' => "Lead marked as converted from {$oldStage->label()}.",
            'performed_at' => now(),
        ]);

        return redirect()->route('leads.show', $lead->id)
            ->with('success', 'Lead converted successfully to Customer/Member pipeline!');
    }

    /**
     * Remove lead
     */
    public function destroy(Lead $lead): RedirectResponse
    {
        $lead->delete();

        return redirect()->route('leads.index')
            ->with('success', 'Lead removed successfully.');
    }
}
