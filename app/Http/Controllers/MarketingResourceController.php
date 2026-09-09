<?php

namespace App\Http\Controllers;

use App\Models\MarketingResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingResourceController extends Controller
{
    /**
     * Store a newly created marketing resource (Super Admin only).
     */
    public function store(Request $request): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can add official resources.');

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'file_type' => 'required|string|in:pdf,image,presentation,doc,spreadsheet,video,link',
            'file_url' => 'required|string|max:500',
            'file_size' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
        ]);

        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['is_active'] = true;
        $validated['created_by'] = auth()->id();

        MarketingResource::create($validated);

        return redirect()->back()->with('success', "Resource '{$validated['title']}' created successfully.");
    }

    /**
     * Update an existing marketing resource (Super Admin only).
     */
    public function update(Request $request, MarketingResource $resource): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can edit official resources.');

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'category' => 'required|string|max:100',
            'file_type' => 'required|string|in:pdf,image,presentation,doc,spreadsheet,video,link',
            'file_url' => 'required|string|max:500',
            'file_size' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $resource->update($validated);

        return redirect()->back()->with('success', "Resource '{$resource->title}' updated successfully.");
    }

    /**
     * Remove an existing marketing resource (Super Admin only).
     */
    public function destroy(MarketingResource $resource): RedirectResponse
    {
        abort_unless(auth()->user()->isSuperAdmin(), 403, 'Only Super Admin can delete official resources.');

        $title = $resource->title;
        $resource->delete();

        return redirect()->back()->with('success', "Resource '{$title}' removed successfully.");
    }
}
