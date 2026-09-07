<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Enums\TaskType;
use App\Models\Activity;
use App\Models\Lead;
use App\Models\Task;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    public function index(Request $request): View
    {
        $filter = $request->query('filter', 'pending'); // 'pending', 'today', 'overdue', 'completed', 'all'
        $type = $request->query('type');

        $query = Task::with(['lead', 'user']);

        if ($type) {
            $query->where('type', $type);
        }

        switch ($filter) {
            case 'today':
                $query->dueToday()->orderBy('priority', 'desc');
                break;
            case 'overdue':
                $query->overdue()->orderBy('due_at', 'asc');
                break;
            case 'completed':
                $query->where('status', TaskStatus::COMPLETED->value)->orderBy('completed_at', 'desc');
                break;
            case 'all':
                $query->orderBy('due_at', 'desc');
                break;
            case 'pending':
            default:
                $query->pending()->orderBy('due_at', 'asc');
                break;
        }

        $tasks = $query->paginate(20)->withQueryString();

        $leads = Lead::select(['id', 'name', 'mobile'])->activePipeline()->orderBy('name')->get();
        $taskTypes = TaskType::cases();
        $priorities = TaskPriority::cases();
        $statuses = TaskStatus::cases();

        $stats = [
            'pending' => Task::pending()->count(),
            'today' => Task::dueToday()->count(),
            'overdue' => Task::overdue()->count(),
            'completed' => Task::where('status', TaskStatus::COMPLETED->value)->count(),
        ];

        return view('tasks.index', compact('tasks', 'leads', 'taskTypes', 'priorities', 'statuses', 'filter', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => ['required', \Illuminate\Validation\Rule::enum(TaskType::class)],
            'related_lead_id' => 'nullable|exists:leads,id',
            'due_at' => 'required|date',
            'priority' => ['required', \Illuminate\Validation\Rule::enum(TaskPriority::class)],
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['related_lead_id'])) Lead::findOrFail($validated['related_lead_id']);

        $task = Task::create([
            'title' => $validated['title'],
            'type' => TaskType::from($validated['type']),
            'related_lead_id' => $validated['related_lead_id'] ?? null,
            'user_id' => Auth::id() ?? 1,
            'due_at' => $validated['due_at'],
            'priority' => TaskPriority::from($validated['priority']),
            'status' => TaskStatus::PENDING,
            'notes' => $validated['notes'] ?? null,
        ]);

        // If related to lead, update lead's next action
        if ($task->related_lead_id) {
            $lead = Lead::find($task->related_lead_id);
            if ($lead) {
                $lead->next_action_type = $task->type->value;
                $lead->next_action_at = $task->due_at;
                $lead->save();

                Activity::create([
                    'lead_id' => $lead->id,
                    'user_id' => Auth::id() ?? 1,
                    'type' => 'task_created',
                    'title' => "Task Scheduled: {$task->title}",
                    'description' => "Due at {$task->due_at->format('d M, Y h:i A')}",
                    'performed_at' => now(),
                ]);
            }
        }

        return back()->with('success', 'Task scheduled successfully!');
    }

    /**
     * Update existing task
     */
    public function update(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => ['required', \Illuminate\Validation\Rule::enum(TaskType::class)],
            'related_lead_id' => 'nullable|exists:leads,id',
            'due_at' => 'required|date',
            'priority' => ['required', \Illuminate\Validation\Rule::enum(TaskPriority::class)],
            'notes' => 'nullable|string',
        ]);

        if (!empty($validated['related_lead_id'])) Lead::findOrFail($validated['related_lead_id']);

        $task->update([
            'title' => $validated['title'],
            'type' => TaskType::from($validated['type']),
            'related_lead_id' => $validated['related_lead_id'] ?? null,
            'due_at' => $validated['due_at'],
            'priority' => TaskPriority::from($validated['priority']),
            'notes' => $validated['notes'] ?? null,
        ]);

        if ($task->related_lead_id) {
            $lead = Lead::find($task->related_lead_id);
            if ($lead) {
                $lead->next_action_type = $task->type->value;
                $lead->next_action_at = $task->due_at;
                $lead->save();
            }
        }

        return back()->with('success', 'Task updated successfully!');
    }

    /**
     * Mark task as completed with outcome & optional next action (Section 9)
     */
    public function complete(Request $request, Task $task): RedirectResponse
    {
        $validated = $request->validate([
            'outcome' => 'required|string|max:255',
            'next_action' => 'nullable|string|max:255',
            'next_action_at' => 'nullable|date',
        ]);

        return \Illuminate\Support\Facades\DB::transaction(function () use ($task, $validated) {
            $task = Task::lockForUpdate()->findOrFail($task->id);
            if ($task->status === TaskStatus::COMPLETED) {
                return back()->with('success', 'This task is already completed.');
            }
    
            $task->status = TaskStatus::COMPLETED;
            $task->completed_at = now();
            $task->outcome = $validated['outcome'];
            $task->next_action = $validated['next_action'] ?? null;
            $task->next_action_at = $validated['next_action_at'] ?? null;
            $task->save();
    
            // If related to a lead, log activity and update lead next action
            if ($task->related_lead_id) {
                $lead = Lead::find($task->related_lead_id);
                if ($lead) {
                    $lead->last_contact_at = now();
    
                    if (! empty($validated['next_action_at'])) {
                        $lead->next_action_type = $validated['next_action'] ?? 'Follow-up';
                        $lead->next_action_at = $validated['next_action_at'];
    
                        // Auto-create next task so follow-up chain is unbroken
                        Task::create([
                            'title' => ($validated['next_action'] ?? 'Follow-up') . ' with ' . $lead->name,
                            'type' => TaskType::FOLLOW_UP,
                            'related_lead_id' => $lead->id,
                            'user_id' => Auth::id() ?? 1,
                            'due_at' => $validated['next_action_at'],
                            'priority' => TaskPriority::HIGH,
                            'status' => TaskStatus::PENDING,
                            'notes' => 'Generated from previous outcome: ' . $validated['outcome'],
                        ]);
                    } else {
                        $lead->next_action_type = null;
                        $lead->next_action_at = null;
                    }
    
                    $lead->calculateScoreAndTemperature();
                    $lead->save();
    
                    Activity::create([
                        'lead_id' => $lead->id,
                        'user_id' => Auth::id() ?? 1,
                        'type' => 'task_completed',
                        'title' => "Task Completed: {$task->title}",
                        'description' => "Outcome: {$validated['outcome']}" . (! empty($validated['next_action']) ? " | Next: {$validated['next_action']}" : ''),
                        'performed_at' => now(),
                    ]);
                }
            }
    
            return back()->with('success', 'Task marked as completed with outcome recorded!');
        }, 3);
    }

    public function destroy(Task $task): RedirectResponse
    {
        $task->delete();

        return back()->with('success', 'Task removed successfully.');
    }
}
