<?php

namespace App\Http\Controllers;

use App\Models\EcosystemLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                  ->orWhere('badge', 'like', "%{$search}%");
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
            'category' => 'required|string|max:100',
            'badge' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['icon'] = $validated['icon'] ?: '🌐';
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = true;

        $link = EcosystemLink::create($validated);

        return redirect()->back()->with('success', "Ecosystem link '{$link->title}' added successfully.");
    }

    /**
     * Update the specified ecosystem link in storage.
     */
    public function update(Request $request, EcosystemLink $ecosystemLink): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'url' => 'required|url|max:255',
            'category' => 'required|string|max:100',
            'badge' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:20',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['icon'] = $validated['icon'] ?: '🌐';
        $validated['is_active'] = $request->has('is_active') ? (bool)$request->is_active : true;

        $ecosystemLink->update($validated);

        return redirect()->back()->with('success', "Link '{$ecosystemLink->title}' updated successfully.");
    }

    /**
     * Remove the specified ecosystem link from storage.
     */
    public function destroy(EcosystemLink $ecosystemLink): RedirectResponse
    {
        $title = $ecosystemLink->title;
        $ecosystemLink->delete();

        return redirect()->back()->with('success', "Link '{$title}' removed successfully.");
    }
}
