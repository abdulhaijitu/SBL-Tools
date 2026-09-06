<?php

namespace App\Http\Controllers;

use App\Enums\LeadStage;
use App\Enums\PresentationOutcome;
use App\Enums\PresentationType;
use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Presentation;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PresentationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Presentation::with(['lead', 'user'])->orderBy('date_time', 'desc');

        $presentations = $query->paginate(15);
        $leads = Lead::orderBy('name')->get();
        $types = PresentationType::cases();
        $outcomes = PresentationOutcome::cases();

        return view('presentations.index', compact('presentations', 'leads', 'types', 'outcomes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'lead_id' => 'required|exists:leads,id',
            'date_time' => 'required|date',
            'type' => 'required|string',
            'topic' => 'nullable|string|max:255',
            'interest_focus' => 'nullable|string|max:255',
            'questions' => 'nullable|string',
            'objections' => 'nullable|string',
            'outcome' => 'nullable|string',
            'next_follow_up_at' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $presentation = Presentation::create([
            'lead_id' => $validated['lead_id'],
            'user_id' => Auth::id() ?? 1,
            'date_time' => $validated['date_time'],
            'type' => PresentationType::from($validated['type']),
            'topic' => $validated['topic'] ?? null,
            'interest_focus' => $validated['interest_focus'] ?? null,
            'questions' => $validated['questions'] ?? null,
            'objections' => $validated['objections'] ?? null,
            'outcome' => ! empty($validated['outcome']) ? PresentationOutcome::from($validated['outcome']) : null,
            'next_follow_up_at' => $validated['next_follow_up_at'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $lead = Lead::find($validated['lead_id']);
        if ($lead) {
            $lead->last_contact_at = now();

            if ($lead->stage === LeadStage::NEW || $lead->stage === LeadStage::CONTACTED || $lead->stage === LeadStage::INTERESTED || $lead->stage === LeadStage::QUALIFIED) {
                $lead->stage = LeadStage::PRESENTATION;
            }

            if ($presentation->outcome === PresentationOutcome::CONVERTED) {
                $lead->stage = LeadStage::CONVERTED;
                $lead->converted_at = now();
            }

            if ($presentation->next_follow_up_at) {
                $lead->next_action_type = 'Post-Presentation Follow-up';
                $lead->next_action_at = $presentation->next_follow_up_at;

                Task::create([
                    'title' => 'Post-Presentation Follow-up with ' . $lead->name,
                    'type' => TaskType::FOLLOW_UP,
                    'related_lead_id' => $lead->id,
                    'user_id' => Auth::id() ?? 1,
                    'due_at' => $presentation->next_follow_up_at,
                    'priority' => TaskPriority::HIGH,
                    'status' => TaskStatus::PENDING,
                    'notes' => 'Questions raised: ' . ($presentation->questions ?? 'None') . ' | Objections: ' . ($presentation->objections ?? 'None'),
                ]);
            }

            $lead->calculateScoreAndTemperature();
            $lead->save();

            Activity::create([
                'lead_id' => $lead->id,
                'user_id' => Auth::id() ?? 1,
                'type' => 'presentation',
                'title' => "Presentation: {$presentation->type->value} - {$presentation->topic}",
                'description' => 'Outcome: ' . ($presentation->outcome?->value ?? 'Scheduled/Pending') . ' | Notes: ' . ($presentation->notes ?? 'N/A'),
                'performed_at' => $presentation->date_time,
            ]);
        }

        return back()->with('success', 'Presentation recorded successfully!');
    }

    public function destroy(Presentation $presentation): RedirectResponse
    {
        $presentation->delete();

        return back()->with('success', 'Presentation session removed.');
    }
}

