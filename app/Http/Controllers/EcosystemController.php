<?php

namespace App\Http\Controllers;

use App\Models\EcosystemLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EcosystemController extends Controller
{
    /**
     * Display a listing of SBL Ecosystem platforms and websites.
     */
    public function index(Request $request): View
    {
        $query = EcosystemLink::where('is_active', true);

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('badge', 'like', "%{$search}%")
                    ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $links = $query->orderBy('sort_order')->orderBy('title')->get();
        $categories = EcosystemLink::distinct()->pluck('category');

        return view('ecosystem.index', compact('links', 'categories'));
    }

    /**
     * Store a newly created ecosystem link in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'url' => 'required|url|max:255',
            'url' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'type' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_official' => 'nullable|boolean',
            'verification_status' => 'nullable|string|in:verified,unverified,needs_review,inactive',
            'is_featured' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'tags' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $rawUrl = trim($validated['url']);
        // Protocol security
        if (preg_match('/^(javascript|data|file|vbscript):/i', $rawUrl)) {
            return redirect()->back()->withErrors(['url' => 'Invalid or unsafe URL protocol.']);
        }

        $normalizedUrl = EcosystemLink::normalizeUrl($rawUrl);
        $validated['url'] = $normalizedUrl;

        // Duplicate URL detection
        $existing = EcosystemLink::where('url', $normalizedUrl)->first();
        if ($existing) {
            return redirect()->back()->withErrors(['url' => "A resource with this URL already exists: '{$existing->title}'."]);
        }

        $validated['icon'] = $validated['icon'] ?: '🌐';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = true;
        $validated['type'] = $validated['type'] ?? 'external';
        $validated['is_official'] = $request->boolean('is_official');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_public'] = $request->has('is_public') ? $request->boolean('is_public') : true;

        $status = $validated['verification_status'] ?? 'unverified';
        $validated['verification_status'] = $status;

        if ($status === 'verified') {
            $validated['verified_at'] = now();
            $validated['verified_by'] = Auth::user()?->name ?? 'Administrator';
        } else {
            $validated['verified_at'] = null;
            $validated['verified_by'] = null;
        }

        $link = EcosystemLink::create($validated);

        return redirect()->back()->with('success', "Ecosystem link '{$link->title}' added successfully.");
        return redirect()->back()->with('success', "Resource '{$link->title}' added successfully.");
    }

    /**
     * Update the specified ecosystem link in storage.
     */
    public function update(Request $request, EcosystemLink $ecosystemLink): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'url' => 'required|url|max:255',
            'url' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'type' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
            'is_official' => 'nullable|boolean',
            'verification_status' => 'nullable|string|in:verified,unverified,needs_review,inactive',
            'is_featured' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'tags' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $rawUrl = trim($validated['url']);
        // Protocol security
        if (preg_match('/^(javascript|data|file|vbscript):/i', $rawUrl)) {
            return redirect()->back()->withErrors(['url' => 'Invalid or unsafe URL protocol.']);
        }

        $normalizedUrl = EcosystemLink::normalizeUrl($rawUrl);
        $validated['url'] = $normalizedUrl;

        // Duplicate URL detection (excluding current record)
        $existing = EcosystemLink::where('url', $normalizedUrl)
            ->where('id', '!=', $ecosystemLink->id)
            ->first();
        if ($existing) {
            return redirect()->back()->withErrors(['url' => "Another resource with this URL already exists: '{$existing->title}'."]);
        }

        $validated['icon'] = $validated['icon'] ?: '🌐';
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;
        $validated['is_active'] = $request->has('is_active') ? $request->boolean('is_active') : true;
        $validated['is_official'] = $request->boolean('is_official');
        $validated['is_featured'] = $request->boolean('is_featured');
        $validated['is_public'] = $request->has('is_public') ? $request->boolean('is_public') : true;

        $newStatus = $validated['verification_status'] ?? $ecosystemLink->verification_status;
        $validated['verification_status'] = $newStatus;

        if ($newStatus === 'verified' && $ecosystemLink->verification_status !== 'verified') {
            $validated['verified_at'] = now();
            $validated['verified_by'] = Auth::user()?->name ?? 'Administrator';
        } elseif ($newStatus !== 'verified') {
            $validated['verified_at'] = null;
            $validated['verified_by'] = null;
        }

        $ecosystemLink->update($validated);

        return redirect()->back()->with('success', "Link '{$ecosystemLink->title}' updated successfully.");
        return redirect()->back()->with('success', "Resource '{$ecosystemLink->title}' updated successfully.");
    }

    /**
     * Remove the specified ecosystem link from storage.
     */
    public function destroy(EcosystemLink $ecosystemLink): RedirectResponse
    {
        $title = $ecosystemLink->title;
        $ecosystemLink->delete();

        return redirect()->back()->with('success', "Link '{$title}' removed successfully.");
        return redirect()->back()->with('success', "Resource '{$title}' removed successfully.");
    }
}
