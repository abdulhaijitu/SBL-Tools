<?php

namespace App\Http\Controllers;

use App\Models\MarketingResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketingResourceController extends Controller
{
    /**
     * Check if current user has permission to manage resources.
     */
    protected function authorizeManage(): void
    {
        $user = Auth::user();
        $canManage = $user && (
            (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) ||
            (method_exists($user, 'hasRole') && $user->hasRole(['super-admin', 'sales-manager'])) ||
            (method_exists($user, 'hasPermission') && $user->hasPermission('marketing.manage'))
        );

        abort_unless($canManage, 403, 'Unauthorized access: You do not have permission to manage official resources.');
    }

    /**
     * Store a newly created marketing resource.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'short_title' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'resource_type' => 'nullable|string|max:50',
            'file_type' => 'required|string|in:pdf,image,presentation,doc,spreadsheet,video,link,zip',
            'file_url' => 'required|string|max:500',
            'thumbnail_url' => 'nullable|string|max:500',
            'file_size' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'version' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:255',
            'is_official' => 'nullable|boolean',
            'verification_status' => 'nullable|string|max:50',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'issued_by' => 'nullable|string|max:200',
            'tags' => 'nullable|string|max:500',
            'language' => 'nullable|string|in:bangla,english,bilingual',
            'is_featured' => 'nullable|boolean',
            'is_counseling_toolkit' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_downloadable' => 'nullable|boolean',
            'is_shareable' => 'nullable|boolean',
            'status' => 'nullable|string|in:current,review_recommended,expired,archived',
            'description' => 'nullable|string|max:1500',
            'notes' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
        ]);

        // Security check for file URL
        $urlLower = strtolower(trim($validated['file_url']));
        if (str_starts_with($urlLower, 'javascript:') || str_starts_with($urlLower, 'data:') || str_starts_with($urlLower, 'file:')) {
            return redirect()->back()->withErrors(['file_url' => 'Invalid or unsafe URL protocol provided.'])->withInput();
        }

        $validated['is_official'] = $request->boolean('is_official', false);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['is_counseling_toolkit'] = $request->boolean('is_counseling_toolkit', false);
        $validated['is_public'] = $request->boolean('is_public', true);
        $validated['is_downloadable'] = $request->boolean('is_downloadable', true);
        $validated['is_shareable'] = $request->boolean('is_shareable', true);
        $validated['sort_order'] = $validated['sort_order'] ?? 0;
        $validated['version'] = $validated['version'] ?? 'v1.0';
        $validated['language'] = $validated['language'] ?? 'bilingual';
        $validated['verification_status'] = $validated['verification_status'] ?? 'needs_verification';
        $validated['status'] = $validated['status'] ?? 'current';
        $validated['is_active'] = true;
        $validated['created_by'] = Auth::id();

        // Verification audit trail
        if (in_array($validated['verification_status'], ['official_verified', 'verified_document'])) {
            $validated['verified_at'] = now();
            $validated['verified_by'] = Auth::user()->name ?? 'Admin';
        }

        MarketingResource::create($validated);

        return redirect()->back()->with('success', "Resource '{$validated['title']}' created successfully.");
    }

    /**
     * Update an existing marketing resource.
     */
    public function update(Request $request, MarketingResource $resource): RedirectResponse
    {
        $this->authorizeManage();

        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'short_title' => 'nullable|string|max:100',
            'category' => 'required|string|max:100',
            'resource_type' => 'nullable|string|max:50',
            'file_type' => 'required|string|in:pdf,image,presentation,doc,spreadsheet,video,link,zip',
            'file_url' => 'required|string|max:500',
            'thumbnail_url' => 'nullable|string|max:500',
            'file_size' => 'nullable|string|max:50',
            'badge' => 'nullable|string|max:50',
            'version' => 'nullable|string|max:20',
            'source' => 'nullable|string|max:255',
            'is_official' => 'nullable|boolean',
            'verification_status' => 'nullable|string|max:50',
            'issue_date' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'issued_by' => 'nullable|string|max:200',
            'tags' => 'nullable|string|max:500',
            'language' => 'nullable|string|in:bangla,english,bilingual',
            'is_featured' => 'nullable|boolean',
            'is_counseling_toolkit' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
            'is_downloadable' => 'nullable|boolean',
            'is_shareable' => 'nullable|boolean',
            'status' => 'nullable|string|in:current,review_recommended,expired,archived',
            'description' => 'nullable|string|max:1500',
            'notes' => 'nullable|string|max:1000',
            'sort_order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $urlLower = strtolower(trim($validated['file_url']));
        if (str_starts_with($urlLower, 'javascript:') || str_starts_with($urlLower, 'data:') || str_starts_with($urlLower, 'file:')) {
            return redirect()->back()->withErrors(['file_url' => 'Invalid or unsafe URL protocol provided.'])->withInput();
        }

        $validated['is_official'] = $request->boolean('is_official', false);
        $validated['is_featured'] = $request->boolean('is_featured', false);
        $validated['is_counseling_toolkit'] = $request->boolean('is_counseling_toolkit', false);
        $validated['is_public'] = $request->boolean('is_public', true);
        $validated['is_downloadable'] = $request->boolean('is_downloadable', true);
        $validated['is_shareable'] = $request->boolean('is_shareable', true);
        $validated['is_active'] = $request->boolean('is_active', true);

        // Update verification timestamps if newly verified
        if (in_array($validated['verification_status'] ?? '', ['official_verified', 'verified_document']) && !$resource->verified_at) {
            $validated['verified_at'] = now();
            $validated['verified_by'] = Auth::user()->name ?? 'Admin';
        }

        $resource->update($validated);

        return redirect()->back()->with('success', "Resource '{$resource->title}' updated successfully.");
    }

    /**
     * Remove or archive an existing marketing resource.
     */
    public function destroy(Request $request, MarketingResource $resource): RedirectResponse
    {
        $this->authorizeManage();

        $title = $resource->title;

        // If archive is requested, soft-archive
        if ($request->input('action') === 'archive') {
            $resource->update([
                'status' => 'archived',
                'is_active' => false,
            ]);
            return redirect()->back()->with('success', "Resource '{$title}' moved to archive.");
        }

        $resource->delete();

        return redirect()->back()->with('success', "Resource '{$title}' removed successfully.");
    }
}
