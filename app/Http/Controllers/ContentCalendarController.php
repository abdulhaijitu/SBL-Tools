<?php

namespace App\Http\Controllers;

use App\Enums\ContentPlatform;
use App\Enums\ContentStatus;
use App\Models\Campaign;
use App\Models\ContentItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ContentCalendarController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $platform = $request->query('platform');

        $query = ContentItem::with(['campaign', 'user'])->orderBy('scheduled_at', 'asc');

        if ($status) {
            $query->where('status', $status);
        }

        if ($platform) {
            $query->where('platform', $platform);
        }

        $items = $query->paginate(20)->withQueryString();

        $campaigns = Campaign::where('status', 'Active')->get();
        $platforms = ContentPlatform::cases();
        $statuses = ContentStatus::cases();

        $stats = [
            'total' => ContentItem::count(),
            'published' => ContentItem::where('status', ContentStatus::PUBLISHED->value)->count(),
            'planned' => ContentItem::whereIn('status', [ContentStatus::PLANNED->value, ContentStatus::READY->value])->count(),
            'leads_generated' => ContentItem::sum('leads_generated'),
        ];

        return view('marketing.content_calendar', compact('items', 'campaigns', 'platforms', 'statuses', 'stats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'platform' => 'required|string',
            'content_type' => 'nullable|string|max:100',
            'topic' => 'nullable|string|max:255',
            'caption' => 'nullable|string',
            'scheduled_at' => 'required|date',
            'status' => 'required|string',
            'cta' => 'nullable|string|max:255',
            'campaign_id' => 'nullable|exists:campaigns,id',
            'notes' => 'nullable|string',
        ]);

        ContentItem::create([
            'campaign_id' => $validated['campaign_id'] ?? null,
            'user_id' => Auth::id() ?? 1,
            'title' => $validated['title'],
            'platform' => ContentPlatform::from($validated['platform']),
            'content_type' => $validated['content_type'] ?? null,
            'topic' => $validated['topic'] ?? null,
            'caption' => $validated['caption'] ?? null,
            'scheduled_at' => $validated['scheduled_at'],
            'status' => ContentStatus::from($validated['status']),
            'cta' => $validated['cta'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Content item scheduled successfully!');
    }

    public function update(Request $request, ContentItem $contentItem): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'reach' => 'nullable|integer',
            'engagement' => 'nullable|integer',
            'inbox_count' => 'nullable|integer',
            'leads_generated' => 'nullable|integer',
            'conversions' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        $contentItem->status = ContentStatus::from($validated['status']);
        if ($contentItem->status === ContentStatus::PUBLISHED && ! $contentItem->published_at) {
            $contentItem->published_at = now();
        }

        $contentItem->reach = $validated['reach'] ?? $contentItem->reach;
        $contentItem->engagement = $validated['engagement'] ?? $contentItem->engagement;
        $contentItem->inbox_count = $validated['inbox_count'] ?? $contentItem->inbox_count;
        $contentItem->leads_generated = $validated['leads_generated'] ?? $contentItem->leads_generated;
        $contentItem->conversions = $validated['conversions'] ?? $contentItem->conversions;
        $contentItem->notes = $validated['notes'] ?? $contentItem->notes;
        $contentItem->save();

        return back()->with('success', 'Content item updated successfully!');
    }

    public function destroy(ContentItem $contentItem): RedirectResponse
    {
        $contentItem->delete();

        return back()->with('success', 'Content item removed successfully.');
    }
}

